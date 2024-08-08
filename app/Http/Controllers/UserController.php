<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    //
    public function index(Request $request)
{
    $searchQuery = $request->input('search', '');
    $ageQuery = $request->input('age', '');
    $sortBy = $request->input('sort_by', 'id'); // Default sort column
    $sortOrder = $request->input('sort_order', 'desc'); // Default sort order

    $users = User::query()
        ->when($searchQuery, function ($queryBuilder) use ($searchQuery) {
            return $queryBuilder->where(function ($subQuery) use ($searchQuery) {
                $subQuery->where('nom', 'like', "%{$searchQuery}%")
                         ->orWhere('prenom', 'like', "%{$searchQuery}%")
                         ->orWhere('email', 'like', "%{$searchQuery}%")
                         ->orWhere('tele', 'like', "%{$searchQuery}%");
            });
        })
        ->when($ageQuery, function ($queryBuilder) use ($ageQuery) {
            return $queryBuilder->where('age', 'like', "%{$ageQuery}%");
        })
        ->orderBy($sortBy, $sortOrder) 
        ->paginate(20); 

    return response()->json($users);
}


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'age' => 'nullable|string|max:3',
            'tele' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = User::create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'age' => $request->age,
            'tele' => $request->tele,
        ]);

        return response()->json($user, 201);
    }

    public function show($id)
    {
        $user = User::find($id);

        if (is_null($user)) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json($user);
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (is_null($user)) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'nom' => 'sometimes|required|string|max:255',
            'prenom' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $id,
            'age' => 'nullable|string|max:3',
            'tele' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        if ($request->has('nom')) {
            $user->nom = $request->nom;
        }
        if ($request->has('prenom')) {
            $user->prenom = $request->prenom;
        }
        if ($request->has('email')) {
            $user->email = $request->email;
        }
        if ($request->has('age')) {
            $user->age = $request->age;
        }
        if ($request->has('tele')) {
            $user->tele = $request->tele;
        }

        $user->save();

        return response()->json($user);
    }

    public function destroy($id)
    {
        $user = User::find($id);

        if (is_null($user)) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }
}



