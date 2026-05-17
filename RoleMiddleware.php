<?php

namespace App\Http\Controllers;


use App\Models\Ads; // Using the Ad model for the 'ads' table
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables; // For DataTables integration
use Illuminate\Support\Carbon; // For date formatting
use Illuminate\Support\Facades\Storage; // For file storage operations (though public_path/File is used for direct public storage)
use Illuminate\Support\Str; // For generating unique filenames
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File; // For file system operations like creating directories


class AdsController extends Controller
{
    // public function store(Request $request)
    // {
    //     // Validate the incoming request data
    //     $validator = Validator::make($request->all(), [
    //         // 'sidebar_image' and 'banner_image' are image files,
    //         // optional (nullable) as per your migration,
    //         // and must be valid image types with a max size of 2048 KB.
    //         'sidebar_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
    //         'banner_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
    //     ]);

    //     // If validation fails, return a JSON response with errors
    //     if ($validator->fails()) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Validation failed',
    //             'errors' => $validator->errors()
    //         ], 422); // HTTP status code 422 for Unprocessable Entity
    //     }

    //     // Get the validated data
    //     $validatedData = $validator->validated();

    //     $sidebarImagePathForDb = null;
    //     $bannerImagePathForDb = null;

    //     $sidebarFileName = null; // To store the filename for potential deletion on error
    //     $bannerFileName = null; // To store the filename for potential deletion on error

    //     // Define the destination path for ad images within public storage
    //     $destinationPath = public_path('storage/ads_images');

    //     // Create the directory if it doesn't exist
    //     // The 0755 permission is standard for directories, 'true' allows recursive creation
    //     if (!File::exists($destinationPath)) {
    //         File::makeDirectory($destinationPath, 0755, true);
    //     }

    //     // Handle sidebar_image upload
    //     if ($request->hasFile('sidebar_image')) {
    //         $file = $request->file('sidebar_image');
    //         // Generate a unique filename to prevent conflicts
    //         $sidebarFileName = time() . '_sidebar_' . Str::random(10) . '.' . $file->getClientOriginalExtension();

    //         // Move the uploaded file to the destination path
    //         $file->move($destinationPath, $sidebarFileName);

    //         // Store the URL for the database or frontend access
    //         $sidebarImagePathForDb = asset('storage/ads_images/' . $sidebarFileName);
    //     }

    //     // Handle banner_image upload
    //     if ($request->hasFile('banner_image')) {
    //         $file = $request->file('banner_image');
    //         // Generate a unique filename for the banner image
    //         $bannerFileName = time() . '_banner_' . Str::random(10) . '.' . $file->getClientOriginalExtension();

    //         // Move the uploaded file to the destination path
    //         $file->move($destinationPath, $bannerFileName);

    //         // Store the URL for the database or frontend access
    //         $bannerImagePathForDb = asset('storage/ads_images/' . $bannerFileName);
    //     }

    //     // Remove the original file objects from validatedData before creating the Ad record
    //     unset($validatedData['sidebar_image']);
    //     unset($validatedData['banner_image']);

    //     // Assign the generated image URLs to the validated data
    //     $validatedData['sidebar_image'] = $sidebarImagePathForDb;
    //     $validatedData['banner_image'] = $bannerImagePathForDb;

    //     try {
    //         // Create a new Ad record in the database
    //         $ad = Ad::create($validatedData);

    //         // Return a success response with the created ad data and image URLs
    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Ad created successfully',
    //             'data' => $ad,
    //             'sidebar_image_url' => $sidebarImagePathForDb,
    //             'banner_image_url' => $bannerImagePathForDb
    //         ], 201); // HTTP status code 201 for Created
    //     } catch (\Exception $e) {
    //         // If there's a database error, attempt to delete the uploaded images
    //         // to prevent orphaned files.

    //         if ($sidebarImagePathForDb && $sidebarFileName) {
    //             $fullPath = public_path('storage/ads_images/' . $sidebarFileName);
    //             if (File::exists($fullPath)) {
    //                 File::delete($fullPath);
    //             }
    //         }

    //         if ($bannerImagePathForDb && $bannerFileName) {
    //             $fullPath = public_path('storage/ads_images/' . $bannerFileName);
    //             if (File::exists($fullPath)) {
    //                 File::delete($fullPath);
    //             }
    //         }

    //         // Return an error response
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Error creating ad: ' . $e->getMessage()
    //         ], 500); // HTTP status code 500 for Internal Server Error
    //     }
    // }

    /**
     * Display a listing of the ads.
     *
     * @return \Illuminate\View\View
     */


    /**
     * Returns data for DataTables.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBannerData(Request $request)
    {
        if ($request->ajax()) {
            $ads = Ads::get(); // Fetch all ads

            return DataTables::of($ads)
                ->addIndexColumn() // Adds a serial number column
                ->addColumn('serial_number', function ($ad) {
                    return $ad->id; // Using the 'id' as serial number for simplicity
                })
                ->addColumn('sidebar_image', function ($ad) {
                    if ($ad->sidebar_image) {
                        // Assuming $ad->sidebar_image in the database is already the public URL
                        // The asset() helper will correctly prepend your domain.
                        return '<img src="' . asset($ad->sidebar_image) . '" alt="Sidebar Image" class="img-thumbnail" style="width: 50px; height: 50px; border-radius: 8px;">';
                    }
                    return '<span class="text-muted">No Sidebar Image</span>';
                })
                ->addColumn('banner_image', function ($ad) {
                    if ($ad->banner_image) {
                        // Assuming $ad->banner_image in the database is already the public URL
                        return '<img src="' . asset($ad->banner_image) . '" alt="Banner Image" class="img-thumbnail" style="width: 50px; height: 50px; border-radius: 8px;">';
                    }
                    return '<span class="text-muted">No Banner Image</span>';
                })
                ->addColumn('created_at', function ($ad) {
                    return $ad->created_at
                        ? $ad->created_at->format('Y-m-d')
                        : 'N/A';
                })
                ->addColumn('action', function ($ad) {
                    // Action buttons for edit and delete
                    $editBtn = '<button class="btn btn-sm btn-primary edit-ad" data-id="' . $ad->id . '" data-bs-toggle="modal" data-bs-target="#adModal">Edit</button>';
                    $deleteBtn = '<button class="btn btn-sm btn-danger delete-ad" data-id="' . $ad->id . '">Delete</button>';

                    return $editBtn . ' ' . $deleteBtn; // Return both buttons
                })
                ->rawColumns(['sidebar_image', 'banner_image', 'action']) // Specify columns that contain raw HTML
                ->make(true);
        }

        // Fallback view if not an AJAX request (though getData is typically only for AJAX)
        return view('pages.ads.index');
    }
    public function index()
    {
        $sideImages = Ads::whereNotNull('sidebar_image')->get();
        $bannerImages = Ads::whereNotNull('banner_image')->get();

        return view('pages.ads.index', compact('sideImages', 'bannerImages'));
    }




    /**
     * Store a newly created ad in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validate the incoming request data
        $validator = Validator::make($request->all(), [
            // 'sidebar_image' and 'banner_image' are image files,
            // optional (nullable) as per your migration,
            // and must be valid image types with a max size of 2048 KB.
            'sidebar_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'banner_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // If validation fails, return a JSON response with errors
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422); // HTTP status code 422 for Unprocessable Entity
        }

        // Get the validated data
        $validatedData = $validator->validated();
        $sidebarImagePathForDb = null;
        $bannerImagePathForDb = null;

        $sidebarFileName = null; // To store the filename for potential deletion on error
        $bannerFileName = null; // To store the filename for potential deletion on error

        // Define the destination path for ad images within public storage
        $destinationPathSidebar = public_path('storage/sidebar_images');
        $destinationPathBanner = public_path('storage/banner_images');

        // Create the directory if it doesn't exist
        // The 0755 permission is standard for directories, 'true' allows recursive creation
        if (!File::exists($destinationPathSidebar)) {
            File::makeDirectory($destinationPathSidebar, 0755, true);
        }
        if (!File::exists($destinationPathBanner)) {
            File::makeDirectory($destinationPathBanner, 0755, true);
        }

        // Handle sidebar_image upload
        if ($request->hasFile('sidebar_image')) {
            $file = $request->file('sidebar_image');
            // Generate a unique filename to prevent conflicts
            $sidebarFileName = time() . '_sidebar_' . Str::random(10) . '.' . $file->getClientOriginalExtension();

            // Move the uploaded file to the destination path
            $file->move($destinationPathSidebar, $sidebarFileName);

            // Store the URL for the database or frontend access
            $sidebarImagePathForDb = asset('storage/sidebar_images/' . $sidebarFileName);
        }

        // Handle banner_image upload
        if ($request->hasFile('banner_image')) {
            $file = $request->file('banner_image');
            // Generate a unique filename for the banner image
            $bannerFileName = time() . '_banner_' . Str::random(10) . '.' . $file->getClientOriginalExtension();

            // Move the uploaded file to the destination path
            $file->move($destinationPathBanner, $bannerFileName);

            // Store the URL for the database or frontend access
            $bannerImagePathForDb = asset('storage/banner_images/' . $bannerFileName);
        }

        // Remove the original file objects from validatedData before creating the Ad record
        unset($validatedData['sidebar_image']);
        unset($validatedData['banner_image']);

        // Assign the generated image URLs to the validated data
        $validatedData['sidebar_image'] = $sidebarImagePathForDb;
        $validatedData['banner_image'] = $bannerImagePathForDb;

        try {
            // Create a new Ad record in the database
            $ad = Ads::create($validatedData);

            // Return a success response with the created ad data and image URLs
            return response()->json([
                'success' => true,
                'message' => 'Ad created successfully',
                'data' => $ad,
                'sidebar_image_url' => $sidebarImagePathForDb,
                'banner_image_url' => $bannerImagePathForDb
            ], 201); // HTTP status code 201 for Created
        } catch (\Exception $e) {
            // If there's a database error, attempt to delete the uploaded images
            // to prevent orphaned files.

            if ($sidebarImagePathForDb && $sidebarFileName) {
                $fullPath = public_path('storage/sidebar_images/' . $sidebarFileName);
                if (File::exists($fullPath)) {
                    File::delete($fullPath);
                }
            }

            if ($bannerImagePathForDb && $bannerFileName) {
                $fullPath = public_path('storage/banner_images/' . $bannerFileName);
                if (File::exists($fullPath)) {
                    File::delete($fullPath);
                }
            }

            // Return an error response
            return response()->json([
                'success' => false,
                'message' => 'Error creating ad: ' . $e->getMessage()
            ], 500); // HTTP status code 500 for Internal Server Error
        }
    }

    /**
     * Update the specified ad in storage, including image update.
     * This method would typically handle PUT/PATCH requests.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // 1. Find the ad
        $ad = Ads::findOrFail($id); // Throws 404 if not found

        // 2. Validate the incoming request data for update
        $validator = Validator::make($request->all(), [
            // 'sidebar_image' and 'banner_image' are nullable for updates,
            // meaning user might not upload a new image for either.
            'sidebar_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'banner_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $validatedData = $validator->validated();

        $sidebarImagePathForDb = $ad->sidebar_image; // Default to existing sidebar image path
        $bannerImagePathForDb = $ad->banner_image;   // Default to existing banner image path

        $sidebarFileName = null; // To store the filename for potential deletion on error
        $bannerFileName = null; // To store the filename for potential deletion on error

        $destinationPath = public_path('storage/ads_images');

        // Handle new sidebar_image upload for update
        if ($request->hasFile('sidebar_image')) {
            $file = $request->file('sidebar_image');
            $sidebarFileName = time() . '_sidebar_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $file->move($destinationPath, $sidebarFileName);
            $sidebarImagePathForDb = asset('storage/ads_images/' . $sidebarFileName);

            // Delete the old sidebar image file from storage if a new one is uploaded
            if ($ad->sidebar_image) {
                // Extract the path relative to the public/storage directory
                $oldSidebarImagePath = str_replace(asset('storage/'), '', $ad->sidebar_image);
                $fullOldSidebarPath = public_path('storage/' . $oldSidebarImagePath);
                if (File::exists($fullOldSidebarPath)) {
                    File::delete($fullOldSidebarPath);
                }
            }
        }

        // Handle new banner_image upload for update
        if ($request->hasFile('banner_image')) {
            $file = $request->file('banner_image');
            $bannerFileName = time() . '_banner_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $file->move($destinationPath, $bannerFileName);
            $bannerImagePathForDb = asset('storage/ads_images/' . $bannerFileName);

            // Delete the old banner image file from storage if a new one is uploaded
            if ($ad->banner_image) {
                // Extract the path relative to the public/storage directory
                $oldBannerImagePath = str_replace(asset('storage/'), '', $ad->banner_image);
                $fullOldBannerPath = public_path('storage/' . $oldBannerImagePath);
                if (File::exists($fullOldBannerPath)) {
                    File::delete($fullOldBannerPath);
                }
            }
        }

        // Prepare data for database update
        unset($validatedData['sidebar_image']);
        unset($validatedData['banner_image']);
        $validatedData['sidebar_image'] = $sidebarImagePathForDb;
        $validatedData['banner_image'] = $bannerImagePathForDb;

        try {
            // Update the Ad record
            $ad->update($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Ad updated successfully',
                'data' => $ad,
                'sidebar_image_url' => $sidebarImagePathForDb,
                'banner_image_url' => $bannerImagePathForDb
            ], 200); // HTTP 200 OK
        } catch (\Exception $e) {
            // Error handling: If database update fails after new file upload, clean up the new file
            if ($sidebarFileName) {
                $fullPath = public_path('storage/ads_images/' . $sidebarFileName);
                if (File::exists($fullPath)) {
                    File::delete($fullPath);
                }
            }
            if ($bannerFileName) {
                $fullPath = public_path('storage/ads_images/' . $bannerFileName);
                if (File::exists($fullPath)) {
                    File::delete($fullPath);
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'Error updating ad: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified ad.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit($id)
    {
        $ad = Ads::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $ad
        ]);
    }

    /**
     * Remove the specified ad from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $ad = Ads::findOrFail($id); // Find the ad by ID, or throw 404

            // Delete the associated sidebar image file from storage (if one exists)
            if ($ad->sidebar_image) {
                // Extract the path relative to the public/storage directory
                $sidebarImagePathInStorage = str_replace(asset('storage/'), '', $ad->sidebar_image);
                $fullSidebarPath = public_path('storage/' . $sidebarImagePathInStorage);
                if (File::exists($fullSidebarPath)) {
                    File::delete($fullSidebarPath);
                }
            }

            // Delete the associated banner image file from storage (if one exists)
            if ($ad->banner_image) {
                // Extract the path relative to the public/storage directory
                $bannerImagePathInStorage = str_replace(asset('storage/'), '', $ad->banner_image);
                $fullBannerPath = public_path('storage/' . $bannerImagePathInStorage);
                if (File::exists($fullBannerPath)) {
                    File::delete($fullBannerPath);
                }
            }

            // Delete the ad record from the database
            $ad->delete();

            return response()->json([
                'success' => true,
                'message' => 'Ad and its images deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete ad: ' . $e->getMessage()
            ], 500); // HTTP 500 Internal Server Error
        }
    }
}
