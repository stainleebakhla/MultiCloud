<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Services\FormatContentService;
use App\Services\ContentService;
use Illuminate\Http\Request;
use \Response;

class ContentController extends Controller {

    protected $formatService;

    protected $contentService;

    public function __construct(FormatContentService $formatService, ContentService $contentService)
    {
        $this->formatService = $formatService;
        $this->contentService = $contentService;

        $this->middleware('clouds.access');
    }

	/**
	 * Display a listing of the resource.
	 *
     * @param  int  $cloudId
	 * @return Response
	 */
	public function index($cloudId)
	{
        return $this->getContents($cloudId, '/');
	}

	/**
	 * Store a newly created resource in storage.
	 *
     * @param  int  $cloudId
	 * @return Response
	 */
	public function store($cloudId)
	{
        return [$cloudId];
	}

	/**
	 * Display the specified resource.
     *
     * @param  int  $cloudId
	 * @param  int  $path
     * @param  Request  $request
	 * @return Response
	 */
	public function show(Request $request, $cloudId, $path)
	{
        $path = $this->preparePath($path);
        if($request->exists('share')) {
            $response = [$this->contentService->shareStart($cloudId, $path)];
        } else {
            $response = $this->getContents($cloudId, $path);

            //When only a file need download
            //return response()->download(storage_path('file.php'));
        }

        return $response;
	}

	/**
	 * Update the specified resource in storage.
	 *
     * @param  Request $request
     * @param  int  $cloudId
     * @param  int  $path
	 * @return Response
	 */
	public function update(Request $request, $cloudId, $path)
	{
        $path = $this->preparePath($path);

        if($request->exists('newCloudId') && $request->exists('newPath')) {
            $response = $this->contentService
                ->taskToMove($cloudId, $path, $request->get('newCloudId'), $request->get('newPath'));
        }
        elseif($request->exists('newPath')) {
            $response = $this->contentService->renameContent($cloudId, $path, $request->get('newPath'));
        }
        else {
            $response = "newPath is necessary param. It's absent!";
        }

        return $response;
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $path
     * @param  int  $cloudId
	 * @return Response
	 */
	public function destroy($cloudId, $path)
	{
        return $this->contentService->removeContent($cloudId, $path);
	}


    private function preparePath($path)
    {
        return str_replace("\\", "/", $path);
    }

    /**
     * @param $cloudId
     * @param $path
     * @return array
     */
    private function getContents($cloudId, $path)
    {
        $contents = $this->contentService->getContents($cloudId, $path);

        $response = $this->formatService->getContents($contents, $cloudId);

        return $response;
    }
}
