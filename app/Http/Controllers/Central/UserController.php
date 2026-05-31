<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\StoreUserRequest;
use App\Http\Requests\Central\UpdateUserRequest;
use App\Http\Resources\Central\UserResource;
use App\Models\Central\User;
use App\Services\Central\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Central platform administrator users.
 */
class UserController extends Controller
{
    public function __construct(
        private readonly UserService $service,
    ) {}

    /**
     * Get paginated User records.
     *
     * @param  Request  $request  Incoming HTTP request.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $items = $this->service->getPaginated($perPage);

        return $this->paginated($items, UserResource::collection($items));
    }

    /**
     * Create a new User.
     *
     * @param  StoreUserRequest  $request  Validated request payload.
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $item = $this->service->create($request->validated());

        return $this->created(new UserResource($item), 'User created successfully.');
    }

    /**
     * Find User by route binding.
     *
     * @param  User  $user  User instance.
     */
    public function show(User $user): JsonResponse
    {
        return $this->success(new UserResource($user));
    }

    /**
     * Update User.
     *
     * @param  UpdateUserRequest  $request  Validated request payload.
     * @param  User  $user  User instance.
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $item = $this->service->update($user, $request->validated());

        return $this->updated(new UserResource($item), 'User updated successfully.');
    }

    /**
     * Delete User.
     *
     * @param  User  $user  User instance.
     */
    public function destroy(User $user): JsonResponse
    {
        $this->service->delete($user);

        return $this->deleted('User deleted successfully.');
    }

    /**
     * Update the user's last login timestamp.
     *
     * @param  User  $user  User instance.
     */
    public function recordLogin(User $user): JsonResponse
    {
        $item = $this->service->recordLogin($user);

        return $this->success(new UserResource($item), 'Login recorded.');
    }

    /**
     * Toggle the user's active flag.
     *
     * @param  User  $user  User instance.
     */
    public function toggleActive(User $user): JsonResponse
    {
        $item = $this->service->toggleActive($user);

        return $this->success(new UserResource($item), 'User active status toggled.');
    }
}
