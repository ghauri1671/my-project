<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Carbon; // Import Carbon for date formatting
use Illuminate\Support\Facades\Storage; // Required for file storage operations
use Illuminate\Support\Str; // Required for generating unique filenames
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;

class AssetController extends Controller
{
    /**
     * Display a listing of the assets.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // You might not need to pass all assets to the view if DataTables handles it via AJAX.
        // However, if you have a form for creating assets, you might pass some initial data or options.
        return view('pages.assets.index');
    }

    /**
     * Returns data for DataTables.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getData(Request $request)
    {
        if ($request->ajax()) {
            $assets = Asset::get(); // Fetch all assets

            return DataTables::of($assets)
                ->addIndexColumn() // Adds a serial number column
                ->addColumn('serial_number', function ($asset) {
                    return $asset->id; // Using the 'id' as serial number for simplicity
                })
                ->addColumn('pair_name', function ($asset) {
                    return $asset->pair_name;
                })
                ->addColumn('market_type', function ($asset) {
                    // Example of dynamic badge based on market_type
                    $badgeClass = match ($asset->market_type) {
                        'forex' => 'bg-primary',
                        'crypto' => 'bg-info',
                        'stock' => 'bg-warning',
                        default => 'bg-secondary',
                    };
                    return '<span class="badge ' . $badgeClass . '">' . ucfirst($asset->market_type) . '</span>';
                })
                ->addColumn('image', function ($asset) {
                    if ($asset->image) {
                        // Assuming $asset->image in the database is already the public URL
                        // e.g., '/storage/assets_images/your_image.jpg'
                        // The asset() helper will correctly prepend your domain.
                        // dd($asset->image); // Debugging line to check the image URL
                        return '<img src="' . asset($asset->image) . '" alt="Asset Image" class="img-thumbnail" style="width: 50px; height: 50px; border-radius: 8px;">';
                    }
                    return '<span class="text-muted">No Image</span>';
                })
                ->addColumn('created_at', function ($asset) {
                    return $asset->created_at
                        ? $asset->created_at->format('Y-m-d')
                        : 'N/A';
                })

                ->addColumn('action', function ($asset) {
                    // Action buttons for edit and delete
                    $editBtn = '<button class="btn btn-sm btn-primary edit-asset" data-id="' . $asset->id . '" data-bs-toggle="modal" data-bs-target="#assetModal">Edit</button>';

                    $deleteBtn = '<button class="btn btn-sm btn-danger delete-asset" data-id="' . $asset->id . '">Delete</button>';



                    return $deleteBtn;


                    // return $editBtn . ' ' . $deleteBtn;
                })
                ->rawColumns(['market_type', 'image', 'action']) // Specify columns that contain raw HTML
                ->make(true);
        }

        // Fallback view if not an AJAX request (though typically getData is only for AJAX)
        return view('pages.assets.index');
    }

    /**
     * Store a newly created asset in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    // public function store(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'pair_name' => 'required|string|max:255',
    //         'market_type' => 'required|string|in:forex,crypto,stock',
    //         'asset_image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // max: 2048 KB = 2 MB
    //     ]);

    //     // If validation fails, return JSON response with errors
    //     if ($validator->fails()) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Validation failed',
    //             'errors' => $validator->errors()
    //         ], 422); // HTTP 422 Unprocessable Entity
    //     }

    //     // Get the validated data
    //     $validatedData = $validator->validated();

    //     $imagePathForDb = null; // Initialize image path variable
    //     $storedFilePath = null; // To keep track of the internal storage path for cleanup

    //     // 2. Handle the image upload if a file is present
    //     if ($request->hasFile('asset_image')) {
    //         $file = $request->file('asset_image');

    //         $fileName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();

    //         $storedFilePath = $file->storeAs('assets_images', $fileName, 'public');


    //         $imagePathForDb = Storage::disk('public')->url($storedFilePath);
    //     }

    //     // 3. Prepare data for database insertion
    //     // Remove the 'asset_image' (UploadedFile object) from validatedData
    //     // and add the 'image' (public URL) which corresponds to your database column.
    //     unset($validatedData['asset_image']); // Remove the file object
    //     $validatedData['image'] = $imagePathForDb; // Assign the public URL to your 'image' column



    //     try {
    //         // 4. Create the Asset record in the database
    //         $asset = Asset::create($validatedData);

    //         // Return success response with the created asset data and image URL
    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Asset created successfully',
    //             'data' => $asset,
    //             'image_url' => $imagePathForDb // Return the public URL for immediate use if needed
    //         ], 201); // HTTP 201 Created
    //     } catch (\Exception $e) {
    //         // 5. Error handling: If database insertion fails, clean up the uploaded file
    //         if ($storedFilePath && Storage::disk('public')->exists($storedFilePath)) {
    //             Storage::disk('public')->delete($storedFilePath);
    //         }

    //         // Return error response
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Error creating asset: ' . $e->getMessage()
    //         ], 500); // HTTP 500 Internal Server Error
    //     }
    // }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pair_name' => 'required|string|max:255',
            'market_type' => 'required|string|in:forex,crypto,stock,indices,commodities',
            'asset_image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $validatedData = $validator->validated();

        $imagePathForDb = null;

        // Save image directly into public/storage/assets_images
        if ($request->hasFile('asset_image')) {
            $file = $request->file('asset_image');
            $fileName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();

            $destinationPath = public_path('storage/assets_images');

            // Create the directory if it doesn't exist
            if (!File::exists($destinationPath)) {
                File::makeDirectory($destinationPath, 0755, true);
            }

            // Move file to public/storage/assets_images
            $file->move($destinationPath, $fileName);

            // URL for DB or frontend access
            $imagePathForDb = asset('storage/assets_images/' . $fileName);
        }

        unset($validatedData['asset_image']);
        $validatedData['image'] = $imagePathForDb;

        try {
            $asset = Asset::create($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Asset created successfully',
                'data' => $asset,
                'image_url' => $imagePathForDb
            ], 201);
        } catch (\Exception $e) {
            // Optionally, delete image on DB error
            if ($imagePathForDb) {
                $fullPath = public_path('storage/assets_images/' . $fileName);
                if (File::exists($fullPath)) {
                    File::delete($fullPath);
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'Error creating asset: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified asset in storage, including image update.
     * This method would typically handle PUT/PATCH requests.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // 1. Find the asset
        $asset = Asset::findOrFail($id); // Throws 404 if not found

        // 2. Validate the incoming request data for update
        $validator = Validator::make($request->all(), [
            'pair_name' => 'required|string|max:255',
            'market_type' => 'required|string|in:forex,crypto,stock,indices,commodities',
            // 'asset_image' is nullable for updates, meaning user might not upload a new image.
            'asset_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'timestamp' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $validatedData = $validator->validated();

        $imagePathForDb = $asset->image; // Default to existing image path
        $storedFilePath = null; // To keep track of the internal storage path for cleanup

        // 3. Handle new image upload for update
        if ($request->hasFile('asset_image')) {
            $file = $request->file('asset_image');
            $fileName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $storedFilePath = $file->storeAs('assets_images', $fileName, 'public');
            $imagePathForDb = Storage::disk('public')->url($storedFilePath);

            // Optional: Delete the old image file from storage if a new one is uploaded
            if ($asset->image) {
                // Extract the path relative to the disk (e.g., assets_images/old_image.jpg)
                // assuming your stored image paths are like /storage/assets_images/filename.jpg
                $oldImagePath = str_replace('/storage/', '', parse_url($asset->image, PHP_URL_PATH));
                if (Storage::disk('public')->exists($oldImagePath)) {
                    Storage::disk('public')->delete($oldImagePath);
                }
            }
        }

        // 4. Prepare data for database update
        unset($validatedData['asset_image']);
        $validatedData['image'] = $imagePathForDb;

        try {
            // 5. Update the Asset record
            $asset->update($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Asset updated successfully',
                'data' => $asset,
                'image_url' => $imagePathForDb
            ], 200); // HTTP 200 OK
        } catch (\Exception $e) {
            // Error handling: If database update fails after new file upload, clean up the new file
            if ($storedFilePath && Storage::disk('public')->exists($storedFilePath)) {
                Storage::disk('public')->delete($storedFilePath);
            }

            return response()->json([
                'success' => false,
                'message' => 'Error updating asset: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Show the form for editing the specified asset.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit($id)
    {
        $asset = Asset::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $asset
        ]);
    }

    /**
     * Remove the specified asset from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $asset = Asset::findOrFail($id); // Find the asset by ID, or throw 404

            // 1. Delete the associated image file from storage (if one exists)
            if ($asset->image) { // Check if the asset has an image path stored
                // Extract the path relative to the disk (e.g., 'assets_images/filename.jpg')
                // Assuming $asset->image stores a path like '/storage/assets_images/filename.jpg'
                $imagePathInStorage = str_replace('/storage/', '', parse_url($asset->image, PHP_URL_PATH));

                // Check if the file actually exists on the 'public' disk before attempting to delete
                if (Storage::disk('public')->exists($imagePathInStorage)) {
                    Storage::disk('public')->delete($imagePathInStorage);
                    // For debugging: error_log("Deleted image: " . $imagePathInStorage);
                }
            }

            // 2. Delete the asset record from the database
            $asset->delete();

            return response()->json([
                'success' => true,
                'message' => 'Asset and its image deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete asset: ' . $e->getMessage()
            ], 500); // HTTP 500 Internal Server Error
        }
    }

}
