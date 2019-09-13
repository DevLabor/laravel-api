<?php

namespace DevLabor\Api\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
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
	 * @var integer|null
	 */
	protected $perPage = null;

	/**
	 * Default sort
	 * @var array
	 */
	protected $defaultSorts = [];

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
	 * Define list of methods with authorization.
	 * @var array
	 */
	protected $authorizeAbilities = [
		'viewAny',
		'view',
		'store',
		'update',
		'destroy'
	];

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
		$this->middleware('auth:api');
	}

	/**
	 * Finds model class name.
	 *
	 * @return Model
	 */
	protected function findModel($modelClass, $id) {
		$model = QueryBuilder::for($modelClass)
                             ->allowedFields($this->getAllowedFields())
		                     ->allowedIncludes($this->getAllowedIncludes())
		                     ->allowedAppends($this->getAllowedAppends())
		                     ->where('id', $id)
		                     ->first();

		if (!$model) {
			throw (new ModelNotFoundException)->setModel($modelClass, $id);
		}

		return $model;
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
	 * Returns default sorts.
	 *
	 * @return array
	 */
	protected function getDefaultSorts() {
		return $this->defaultSorts;
	}

	/**
	 * Returns allowed methods.
	 *
	 * @return array
	 */
	protected function getAuthorizeAbilities() {
		return $this->authorizeAbilities;
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
	 * Returns true if method should authorize.
	 *
	 * @param $ability
	 *
	 * @return bool
	 */
	protected function shouldAuthorize($ability) {
		$abilities = $this->getAuthorizeAbilities();
		if (!is_array($abilities)) {
			return false;
		}

		return in_array($ability, $abilities);
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
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function index()
	{
		$modelClass = $this->guessModelClass();
		$resourceClass = $this->guessResourceClass();

		try {
			// authorize permission
			if ($this->shouldAuthorize('viewAny')) {
				$this->authorize( 'viewAny', $modelClass );
			}
		}
		catch (\Exception $e) {
			return $this->getErrorResponse($e->getMessage());
		}

		$results = QueryBuilder::for($modelClass)
                                ->allowedFields($this->getAllowedFields())
                                ->allowedIncludes($this->getAllowedIncludes())
                                ->allowedFilters($this->getAllowedFilters())
                                ->defaultSorts($this->getDefaultSorts())
                                ->allowedSorts($this->getAllowedSorts())
                                ->allowedAppends($this->getAllowedAppends())
                                ->where($this->getWhereClauses());

		return $resourceClass::collection($results->paginate(($this->perPage ? : config('api.pagination.items', 20)))->appends( \Illuminate\Support\Facades\Request::except('page') ) );
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

		$validation = Validator::make($request->all(), $this->getValidationRules('store'));

		if($validation->fails()){
			return $this->getErrorResponse($validation->errors());
		}

		try {
			// authorize permission
			if ($this->shouldAuthorize(['store', 'create'])) {
				$this->authorize( 'store', $modelClass );
			}

			$validated = $this->creating($request, $validation->validated());

			if ($validated) {
				$model = $modelClass::create( $validated );
				$model = $this->saved( $request, $model, $validated );
			}
		}
		catch (\Exception $e) {
			return $this->getErrorResponse($e->getMessage());
		}

		return $this->getJsonResponse($model ?? null, Response::HTTP_CREATED);
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
			$model = $this->findModel($modelClass, $id);

			// authorize permission
			if ($this->shouldAuthorize('view')) {
				$this->authorize( 'view', $model );
			}

			if (!$model) {
				throw (new ModelNotFoundException)->setModel($modelClass, $id);
			}
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
			$model = $this->findModel($modelClass, $id);

			// authorize permission
			if ($this->shouldAuthorize(['update', 'edit'])) {
				$this->authorize( 'update', $model );
			}

			$validated = $this->updating($request, $model, $validation->validated());

			if ($validated) {
				$model->update( $validated );
				$model = $this->saved( $request, $model, $validated );
			}
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
			$model = $this->findModel($modelClass, $id);

			// authorize permission
			if ($this->shouldAuthorize('destroy')) {
				$this->authorize( 'destroy', $model );
			}

			$model->delete();
		}
		catch (\Exception $e) {
			return $this->getErrorResponse($e->getMessage());
		}

		return $this->getJsonResponse(null, Response::HTTP_OK);
	}

	/**
	 * Executed after creating or updating.
	 *
	 * @param Request $request
	 * @param null $model
	 * @param array $validated
	 * @return Model|null
	 */
	protected function saved(Request $request, $model = null, $validated = []) {
		return $model;
	}

	/**
	 * Executed before creating.
	 *
	 * @param Request $request
	 * @param array $validated
	 * @return array
	 */
	protected function creating(Request $request, $validated = []) {
		return $validated;
	}

	/**
	 * Executed before updating.
	 *
	 * @param Request $request
	 * @param Model|null $model
	 * @param array $validated
	 * @return array
	 */
	protected function updating(Request $request, $model = null, $validated = []) {
		return $validated;
	}
}
