<?php

namespace DevLabor\Api\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Spatie\QueryBuilder\QueryBuilder;

class ApiController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    const AUTHORIZE_KEY_INDEX = 'viewAny';
    const AUTHORIZE_KEY_SHOW = 'view';
    const AUTHORIZE_KEY_STORE = 'store';
    const AUTHORIZE_KEY_UPDATE = 'update';
    const AUTHORIZE_KEY_DESTROY = 'destroy';

    /**
     * Models location
     * @var string
     */
    protected $modelPath = 'App\\Models\\';

    /**
     * Resources location
     * @var string
     */
    protected $resourcePath = 'App\\Http\\Resources\\';

    /**
     * Controller append name
     * @var string[]
     */
    protected $appendName = ['ApiController', 'Controller'];

    /**
     * Guessed model class.
     * @var string
     */
    protected $modelClass = '';

    /**
     * Guessed resource class.
     * @var string
     */
    protected $resourceClass = '';

    /**
     * Posts per api call
     * @var int|null
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
     * List of allowed fields
     * @var array
     */
    protected $allowedFields = [];

    /**
     * Define list of methods with authorization.
     * @var array
     */
    protected $authorizeAbilities = [
        self::AUTHORIZE_KEY_INDEX,
        self::AUTHORIZE_KEY_SHOW,
        self::AUTHORIZE_KEY_STORE,
        self::AUTHORIZE_KEY_UPDATE,
        self::AUTHORIZE_KEY_DESTROY,
    ];

    /**
     * Validation rules for store and update.
     * @var array
     */
    protected $validationRules = [
        //...
        self::AUTHORIZE_KEY_STORE => [
            //...
        ],
        self::AUTHORIZE_KEY_UPDATE => [
            //...
        ],
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
     * @param $className
     * @param $id
     *
     * @return Model|null
     */
    protected function findModel(string $className, int $id)
    {
        $model = QueryBuilder::for($className)
                             ->allowedFields($this->getAllowedFields())
                             ->allowedIncludes($this->getAllowedIncludes())
                             ->where('id', $id)
                             ->first();

        if (! $model) {
            throw (new ModelNotFoundException)->setModel($className, $id);
        }

        return $model;
    }

    /**
     * Guess model class name.
     *
     * @return string
     */
    protected function guessModelClass()
    {
        if (empty($this->modelClass)) {
            $this->modelClass = $this->modelPath . str_replace($this->appendName, '', class_basename(get_class($this)));
        }

        return $this->modelClass;
    }

    /**
     * Guesses resource class name.
     *
     * @return string
     */
    protected function guessResourceClass()
    {
        if (empty($this->resourceClass)) {
            return $this->resourcePath . (class_basename($this->modelClass) ? : str_replace($this->appendName, '', class_basename(get_class($this))));
        }

        return $this->resourceClass;
    }

    /**
     * Returns allowed includes.
     *
     * @return array
     */
    protected function getAllowedIncludes()
    {
        return $this->allowedIncludes;
    }

    /**
     * Returns allowed filters.
     *
     * @return array
     */
    protected function getAllowedFilters()
    {
        return $this->allowedFilters;
    }

    /**
     * Returns allowed sort fields.
     *
     * @return array
     */
    protected function getAllowedSorts()
    {
        return $this->allowedSorts;
    }

    /**
     * Returns allowed sort fields.
     *
     * @return array
     */
    protected function getAllowedFields()
    {
        return $this->allowedFields;
    }

    /**
     * Returns default sorts.
     *
     * @return array
     */
    protected function getDefaultSorts()
    {
        return $this->defaultSorts;
    }

    /**
     * Returns allowed methods.
     *
     * @return array
     */
    protected function getAuthorizeAbilities()
    {
        return $this->authorizeAbilities;
    }

    /**
     * Returns custom where clauses
     *
     * @return array
     */
    protected function getWhereClauses()
    {
        return [];
    }

    /**
     * Returns true if method should authorize.
     *
     * @param string|string[] $ability
     *
     * @return bool
     */
    protected function shouldAuthorize($ability)
    {
        $abilities = $this->getAuthorizeAbilities();
        if (empty($abilities)) {
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
    protected function getValidationRules(string $action)
    {
        $rules = $this->validationRules;

        unset($rules[self::AUTHORIZE_KEY_STORE]);
        unset($rules[self::AUTHORIZE_KEY_UPDATE]);

        if ($actionRules = data_get($this->validationRules, $action)) {
            return array_merge($rules, $actionRules);
        }

        return $rules;
    }

    /**
     * Returns per page count.
     *
     * @return \Illuminate\Config\Repository|mixed
     */
    protected function getPerPage()
    {
        return request()->input('per_page', ($this->perPage ? : config('api.pagination.items', 20)));
    }

    /**
     * Returns response as json data.
     *
     * @param Model|null $model
     * @param int $statusCode
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function getJsonResponse(?Model $model, $statusCode = Response::HTTP_OK)
    {
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
     * @param \Illuminate\Support\MessageBag|string $message
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function getErrorResponse($message)
    {
        return response()->json([
            'error' => true,
            'message' => $message,
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $className = $this->guessModelClass();
        $resourceClass = $this->guessResourceClass();

        try {
            // authorize permission
            if ($this->shouldAuthorize(self::AUTHORIZE_KEY_INDEX)) {
                $this->authorize(self::AUTHORIZE_KEY_INDEX, $className);
            }
        } catch (\Exception $e) {
            return $this->getErrorResponse($e->getMessage());
        }

        $results = QueryBuilder::for($className)
                                ->allowedFields($this->getAllowedFields())
                                ->allowedIncludes($this->getAllowedIncludes())
                                ->allowedFilters($this->getAllowedFilters())
                                ->defaultSorts($this->getDefaultSorts())
                                ->allowedSorts($this->getAllowedSorts())
                                ->where($this->getWhereClauses());

        if ($request->input('limit')) {
            $limit = intval($request->input('limit'));
            $pageName = 'page';

            return $resourceClass::collection((new LengthAwarePaginator($results->limit($limit)->get(), $limit, $this->getPerPage(), Paginator::resolveCurrentPage($pageName), [
                'path' => Paginator::resolveCurrentPath(),
                'pageName' => $pageName,
            ]))->appends(\Illuminate\Support\Facades\Request::except('page')));
        }

        return $resourceClass::collection($results->paginate($this->getPerPage())
                                                    ->appends(\Illuminate\Support\Facades\Request::except('page')));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $className = $this->guessModelClass();

        $validation = Validator::make($request->all(), $this->getValidationRules(self::AUTHORIZE_KEY_STORE));

        if ($validation->fails()) {
            return $this->getErrorResponse($validation->errors());
        }

        try {
            // authorize permission
            if ($this->shouldAuthorize([self::AUTHORIZE_KEY_STORE, 'create'])) {
                $this->authorize(self::AUTHORIZE_KEY_STORE, $className);
            }

            $validated = $this->creating($request, $validation->validated());

            if ($validated) {
                $model = $className::create($validated);
                $model = $this->saved($request, $model, $validated);
            }
        } catch (\Exception $e) {
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
        $className = $this->guessModelClass();

        try {
            $model = $this->findModel($className, $id);

            // authorize permission
            if ($this->shouldAuthorize(self::AUTHORIZE_KEY_SHOW)) {
                $this->authorize(self::AUTHORIZE_KEY_SHOW, $model);
            }

            if (! $model) {
                throw (new ModelNotFoundException)->setModel($className, $id);
            }
        } catch (\Exception $e) {
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
        $className = $this->guessModelClass();

        // validate given request
        $validation = Validator::make($request->all(), $this->getValidationRules(self::AUTHORIZE_KEY_UPDATE));

        if ($validation->fails()) {
            return $this->getErrorResponse($validation->errors());
        }

        try {
            $model = $this->findModel($className, $id);

            // authorize permission
            if ($this->shouldAuthorize([self::AUTHORIZE_KEY_UPDATE, 'edit'])) {
                $this->authorize(self::AUTHORIZE_KEY_UPDATE, $model);
            }

            if (! $model) {
                throw (new ModelNotFoundException)->setModel($className, $id);
            }

            $validated = $this->updating($request, $model, $validation->validated());

            if ($validated) {
                $model->update($validated);
                $model = $this->saved($request, $model, $validated);
            }
        } catch (\Exception $e) {
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
        $className = $this->guessModelClass();

        try {
            $model = $this->findModel($className, $id);

            // authorize permission
            if ($this->shouldAuthorize(self::AUTHORIZE_KEY_DESTROY)) {
                $this->authorize(self::AUTHORIZE_KEY_DESTROY, $model);
            }

            if (! $model) {
                throw (new ModelNotFoundException)->setModel($className, $id);
            }

            $model->delete();
        } catch (\Exception $e) {
            return $this->getErrorResponse($e->getMessage());
        }

        return $this->getJsonResponse(null, Response::HTTP_OK);
    }

    /**
     * Executed after creating or updating.
     *
     * @param Request $request
     * @param Model|null $model
     * @param array $validated
     *
     * @return Model|null
     */
    protected function saved(Request $request, ?Model $model, $validated = [])
    {
        return $model;
    }

    /**
     * Executed before creating.
     *
     * @param Request $request
     * @param array $validated
     * @return array
     */
    protected function creating(Request $request, $validated = [])
    {
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
    protected function updating(Request $request, $model = null, $validated = [])
    {
        return $validated;
    }
}
