<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Session\createSession;
use App\Models\Salon;
use App\Models\Showtime;
use Illuminate\Http\Request;
use Throwable;

class ShowtimeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('sessions', ['view-sessions']);

        try {
            $sessions = Showtime::latest()->get();

            return view('sessions.index', compact('sessions'));
        } catch (Throwable $e) {
            return back()->with('error', 'Somthing went wrong, try again later');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('sessions', ['create-sessions']);

        try {
            $salons = Salon::latest()->get();

            return view('sessions.create', compact('salons'));
        } catch (Throwable $e) {
            return back()->with('error', 'Somthing went wrong, try again later');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(createSession $request)
    {
        $this->authorize('sessions', ['create-sessions']);

        try {
            $session = Showtime::create($request->validated());

            if ($request->has('salons')) {
                $session->salons()->sync($request->input('salons'));
            }

            return redirect()->route('sessions.index')->with('success', 'Session created successfully');
        } catch (Throwable $e) {
            return back()->with('error', 'Somthing went wrong, try again later');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Showtime $showtime)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Showtime $showtime)
    {
        $this->authorize('sessions', ['edit-sessions']);

        $salons = Salon::latest()->get();

        return view('sessions.edit', compact('showtime', 'salons'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(createSession $request, Showtime $showtime)
    {
        $this->authorize('sessions', ['edit-sessions']);

        try {
            $showtime->update($request->validated());


            $showtime->salons()->sync($request->input('salons'));

            return redirect()->route('sessions.index');
        } catch (Throwable $e) {
            return back()->with('error', 'Somthing went wrong, try again later');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Showtime $showtime)
    {
        $this->authorize('sessions', ['delete-sessions']);

        try {
            $showtime->delete();

            return redirect()->route('sessions.index');
        } catch (Throwable $e) {
            return back()->with('error', 'Somthing went wrong, try again later');
        }
    }
}
