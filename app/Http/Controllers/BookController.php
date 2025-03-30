<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\Storage;
use App\Rules\Base64Image;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

class BookController extends Controller implements HasMiddleware
{
    public static function middleware()
    {
        return [
            new Middleware('auth:sanctum', except: ['index', 'show'])
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $page = $request->query('page', 1);
        $limit = $request->query('limit', 5);
        $skip = ($page - 1) * $limit;

        $books = Book::with(['user:id,username,profileImage'])
            ->orderBy('created_at', 'desc')
            ->skip($skip)
            ->take($limit)
            ->get();

        $totalBook = Book::count();

        return response()->json([
            'books' => $books,
            'currentPage' => (int) $page,
            'totalBook' => $totalBook,
            'totalPages' => ceil($totalBook / $limit),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => ['required', 'string', 'max:255'],
            'caption' => ['required'],
            'rating' => ['required'],
            'image' => ['required', 'string', new Base64Image()],
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        if ($request->has('imageDataUrl')) {
            [$metaData, $imageData] = explode(',', $request->imageDataUrl, 2);
            $imageType = str_replace(['data:image/', ';base64'], '', $metaData);
            $decodedImage = base64_decode($imageData);
            if ($decodedImage !== false) {
                // Generate unique filename
                $fileName = time() . '.' . $imageType;
                $filePath = $fileName;
                // Save image to 'images' disk
                Storage::disk('images')->put($filePath, $decodedImage);
                // Generate full URL
                $fileUrl = asset('storage/images/' . $filePath);
            } else {
                return response()->json([
                    'message' => 'No valid Image found'
                ], 422);
            }
        } else {
            return response()->json([
                'message' => 'No valid Image provided'
            ], 422);
        }
        $validatedData = $validator->validated();
        $validatedData['image'] = $fileUrl;
        $book = $request->user()->books()->create($validatedData);
        return response()->json([
            'message' => 'Resource created successfully!',
            'book' => $book,
            'user' => $book->user,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Book $book)
    {
        return response()->json([
            'message' => 'Resource found',
            'book' => $book,
            'user' => $book->user,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Book $book)
    {
        Gate::authorize('modify', $book);
        $validator = Validator::make($request->all(), [
            'title' => ['required', 'string', 'max:255'],
            'caption' => ['required'],
            'rating' => ['required'],
            'image' =>  ['required', 'string', new Base64Image()],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        if ($request->has('imageDataUrl')) {
            [$metaData, $imageData] = explode(',', $request->imageDataUrl, 2);
            $imageType = str_replace(['data:image/', ';base64'], '', $metaData);
            $decodedImage = base64_decode($imageData);
            if ($decodedImage !== false) {
                // Generate unique filename
                $fileName = time() . '.' . $imageType;
                $filePath = $fileName;
                // Remove the existing file
                $oldFilePath = basename($book->image);
                // delet the existing image file
                if (Storage::disk('images')->exists($oldFilePath)) {
                    Storage::disk('images')->delete($oldFilePath);
                }
                // Save image to 'images' disk
                Storage::disk('images')->put($filePath, $decodedImage);
                // Generate full URL
                $fileUrl = asset('storage/images/' . $filePath);
            } else {
                return response()->json([
                    'message' => 'No valid Image found'
                ], 422);
            }
        } else {
            return response()->json([
                'message' => 'No valid Image provided'
            ], 422);
        }
        $validatedData = $validator->validated();
        $validatedData['image'] = $fileUrl;
        $book->update($validatedData);
        return response()->json([
            'message' => 'Resource update successfully!',
            'book' => $book,
            'user' => $book->user,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Book $book)
    {
        $filePath = basename($book->image);
        Gate::authorize('modify', $book);
        // delet the existing image file
        if (Storage::disk('images')->exists($filePath)) {
            Storage::disk('images')->delete($filePath);
        }
        $book->delete();
        return response()->json(['message' => 'The Book was deleted'], 200);
    }
    public function userBooks(Request $request)
    {
        try {
            $user = $request->user(); // Get authenticated user
            $books = Book::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json($books);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Internal server error'], 500);
        }
    }
}
