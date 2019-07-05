<?php

namespace DevLabor\Api\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Spatie\QueryBuilder\QueryBuilder;

class ApiController extends Controller
{
	/**
	 * Guessed model class.
	 * @var string
	 */
	protected $modelClass = null;

	/**
	 * Posts per api call
	 * @var array
	 */
	protected $perPage = 20;

	/**
	 * List of allowed includes
	 * @var array
	 */
	protected $allowedIncludes = [];

	/**
	 * List of allowed filters
	 * @var array
	 */
	protected $allowedFilters = [];

	/**
	 * List of allowed sorts
	 * @var array
	 */
	protected $allowedSorts = [];

	/**
	 * List of allowed appends
	 * @var array
	 */
	protected $allowedAppends = [];

	/**
	 * List of allowed fields
	 * @var array
	 */
	protected $allowedFields = [];

	/**
	 * Validation rules for store and update.
	 * @var array
	 */
	protected $validationRules = [
		//...
		'store' => [
			//...
		],
		'update' => [
			//...
		]
	];

	/**
	 * ApiController constructor.
	 */
	public function __construct() 
	{
		$this->perPage = config('api.pagination.count', 20);
		
		$this->middleware('auth:api')->except('index', 'show');
	}

	/**
	 * Guess model class name.
	 *
	 * @return null|string
	 */
	protected function guessModelClass() {
		if (empty($this->modelClass)) {
			$this->modelClass = 'App\\' . str_replace('ApiController', '', class_basename(get_class($this)));
		}

		return $this->modelClass;
	}

	/**
	 * Guesses resource collection class name.
	 *
	 * @return string
	 */
	protected function guessResourceCollectionClass() {
		return $this->guessResourceClass() . 'Collection';
	}

	/**
	 * Guesses resource class name.
	 *
	 * @return string
	 */
	protected function guessResourceClass() {
		return 'App\\Http\\Resources\\' . ( class_basename($this->modelClass) ? : str_replace('ApiController', '', class_basename(get_class($this))));
	}

	/**
	 * Returns allowed includes.
	 *
	 * @return array
	 */
	protected function getAllowedIncludes() {
		return $this->allowedIncludes;
	}

	/**
	 * Returns allowed filters.
	 *
	 * @return array
	 */
	protected function getAllowedFilters() {
		return $this->allowedFilters;
	}

	/**
	 * Returns allowed sort fields.
	 *
	 * @return array
	 */
	protected function getAllowedSorts() {
		return $this->allowedSorts;
	}

	/**
	 * Returns allowed sort fields.
	 *
	 * @return array
	 */
	protected function getAllowedAppends() {
		return $this->allowedAppends;
	}

	/**
	 * Returns allowed sort fields.
	 *
	 * @return array
	 */
	protected function getAllowedFields() {
		return $this->allowedFields;
	}

	/**
	 * Returns custom where clauses
	 *
	 * @return null
	 */
	protected function getWhereClauses() {
		return [];
	}

	/**
	 * Returns validation rules for store, update or delete. Allows defining of global rules.
	 *
	 * @param   $action   string  store|update|delete
	 * @return  array
	 */
	protected function getValidationRules($action) {
		$validationRules = $this->validationRules;

		unset($validationRules['store']);
		unset($validationRules['update']);

		if ($actionRules = data_get($this->validationRules, $action)) {
			return array_merge($validationRules, $actionRules);
		}

		return $validationRules;
	}

	/**
	 * Returns response as json data.
	 *
	 * @param $model
	 * @param int $statusCode
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	protected function getJsonResponse($model, $statusCode = Response::HTTP_OK) {
		$resource = null;

		if ($model) {
			$resourceClass = $this->guessResourceClass();
			$resource = new $resourceClass($model);
		}

		return response()->json(($resource ? $resource->toArray(request()) : []), $statusCode);
	}

	/**
	 * Returns error message.
	 *
	 * @param $message
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	protected function getErrorResponse($message) {
		return response()->json([
			'error' => true,
			'message' => $message
		], Response::HTTP_BAD_REQUEST);
	}
	
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
	    $modelClass = $this->guessModelClass();
	    $collectionClass = $this->guessResourceCollectionClass();

	    // authorize permission
	    $this->authorizeResource($modelClass);

	    $results = QueryBuilder::for($modelClass)
	    	        ->allowedIncludes($this->getAllowedIncludes())
	                ->allowedFilters($this->getAllowedFilters())
	                ->allowedSorts($this->getAllowedSorts())
		            ->allowedAppends($this->getAllowedAppends())
		            ->allowedFields($this->getAllowedFields())
	                ->where($this->getWhereClauses());

	    return new $collectionClass($results->paginate($this->perPage)->appends( Input::except('page') ) );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
	    $modelClass = $this->guessModelClass();

	    // authorize permission
	    $this->authorizeResource($modelClass);

	    $validation = Validator::make($request->all(), $this->getValidationRules('store'));

	    if($validation->fails()){
			return $this->getErrorResponse($validation->errors());
	    }

	    try {
		    $model = $modelClass::create($validation->validated());
	    }
	    catch (\Exception $e) {
		    return $this->getErrorResponse($e->getMessage());
	    }

	    return $this->getJsonResponse($model, Response::HTTP_OK);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
	    $modelClass = $this->guessModelClass();

	    try {
		    $model = QueryBuilder::for($modelClass)
		                ->allowedIncludes($this->getAllowedIncludes())
		                ->allowedAppends($this->getAllowedAppends())
		                ->allowedFields($this->getAllowedFields())
			            ->where('id', $id)
			            ->first();

		    // authorize permission
		    $this->authorizeResource($model);
	    }
	    catch (\Exception $e) {
		    return $this->getErrorResponse($e->getMessage());
	    }

	    return $this->getJsonResponse($model);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
	    $modelClass = $this->guessModelClass();

	    // validate given request
	    $validation = Validator::make($request->all(), $this->getValidationRules('update'));

	    if($validation->fails()){
		    return $this->getErrorResponse($validation->errors());
	    }

	    try {
		    $model = $modelClass::findOrFail($id);

		    // authorize permission
		    $this->authorizeResource($model);

		    $model->update($validation->validated());
	    }
	    catch (\Exception $e) {
		    return $this->getErrorResponse($e->getMessage());
	    }

	    return $this->getJsonResponse($model, Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
	    $modelClass = $this->guessModelClass();

	    try {
		    $model = $modelClass::findOrFail($id);

		    // authorize permission
		    $this->authorizeResource($model);

		    $model->delete();
	    }
	    catch (\Exception $e) {
    		return $this->getErrorResponse($e->getMessage());
	    }

        return $this->getJsonResponse(null, Response::HTTP_OK);
    }
}
