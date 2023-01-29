<?php

namespace App\Http\Controllers;

use App\{Profession, Role, Skill, Sortable, User, UserFilter};
use App\Http\Requests\{CreateUserRequest, UpdateUserRequest};

class UserController extends Controller
{
    public function index(UserFilter $userFilter, Sortable $sortable)
    {
        $users = User::query()
            ->when(request()->routeIs('users.trashed'), function ($q) {
                $q->onlyTrashed();
            })
            ->with('team', 'skills', 'profile.profession')
            ->when(request('team'), function ($query, $team){
                if($team === 'with_team'){
                    $query->has('team');
                } else if ($team === 'without_team'){
                    $query->doesntHave('team');
                }
            })
            ->filterBy($userFilter, request()->only(['state', 'role', 'search', 'skills', 'from', 'to']))
            ->when(request('order'), function ($q) {
                $q->orderBy(request('order'), request('direction', 'asc'));
            }, function ($q) {
               // $q->orderBy('created_at', 'desc');
                $q->orderByDesc('created_at');
            })
            ->paginate();

        $users->appends($userFilter->valid());

        $sortable->setCurrentOrder(request('order'), request('direction'));

        return view('users.index', [
            'users' => $users,
            'skills' => Skill::orderBy('name')->get(),
            'view' => request()->routeIs('users.trashed') ? 'trash' : 'index',
            'checkedSkills' => collect(request('skills')),
            'sortable' => $sortable,
        ]);
    }

    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    protected function form($view, User $user)
    {
        return view($view, [
            'user' => $user,
            'professions' => Profession::orderBy('title', 'ASC')->get(),
            'skills' => Skill::orderBy('name', 'ASC')->get(),
        ]);
    }

    public function create()
    {
       return $this->form('users.create', new User);
    }

    public function store(CreateUserRequest $request)
    {
        $request->createUser();

        return redirect()->route('users.index');
    }

    public function edit(User $user)
    {
        return $this->form('users.edit', $user);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $request->updateUser($user);

        return redirect()->route('users.show', ['user' => $user]);
    }

    public function trash(User $user)
    {
        $user->delete();
        $user->profile()->delete(); // elimina el perfil de forma logica.

        return redirect()->route('users.index');
    }

    public function destroy(int $id)
    {
        $user = User::onlyTrashed()->where('id', $id)->firstOrFail();

        $user->forceDelete();

        return redirect()->route('users.trashed');
    }

    public function restore(int $id)
    {
        $user = User::onlyTrashed()->where('id', $id)->firstOrFail();

        $user->restore();
        $user->profile()->restore();

        return redirect()->route('users.trashed');
    }

}