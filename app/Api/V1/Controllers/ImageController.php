<?php

namespace App\Api\V1\Controllers;

use Dingo\Api\Routing\Helpers;
use App\Api\V1\Requests\ImageRequest;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\Request;
use App\User;
use App\Image;

use Storage;

class ImageController extends Controller
{
    use Helpers;

    //get latest images
    public function get_latest_images(Request $request)
    {
        $count_per_page = $request->get('count_per_page');
        return Image::orderBy('created_at','DESC')->paginate($count_per_page);
    }

    //get user's images
    public function get_user_images(Request $request){
        $user = $_SESSION['user'];
        $userid = $user->id;
        $count_per_page = $request->get('count_per_page');
        return Image::where('user_id', '=', $userid)->orderBy('created_at','DESC')->paginate($count_per_page);
    }

    //get near images
    public function get_images_near(Request $request){
        $lat = $request->get('lat');
        $lon = $request->get('lon');
        $radius = $request->get('radius');
        
        $user = $_SESSION['user'];
        $userid = $user->id;

        $count_per_page = $request->get('count_per_page');

        $images = Image::where('user_id', '=', $userid)
            ->selectRaw('( 3959 * acos( cos( radians(?) ) *
                                cos( radians( lat ) )
                                * cos( radians( lng ) - radians(?)
                                ) + sin( radians(?) ) *
                                sin( radians( lat ) ) )
                                ) AS distance', [$lat, $lon, $lat])
            ->havingRaw("distance < ?", [$radius])
            ->paginate($count_per_page);
    
        return images;
    }

    //post image
    public function store(ImageRequest $request){
        $user = $_SESSION['user'];
    
        $image = new Image;
    
        $image->description = $request->get('description');
        $image->location = $request->get('location');
        $image->lat = $request->get('lat');
        $image->lon = $request->get('lon');
        
        //$image->s3_path = $this->uploadFileToS3($request);
        $image->s3_path = $request->get('s3_path');

        if($user->images()->save($image))
            return response()
            ->json([
                'status' => 'ok',
                'status_code' =>  200
            ]);
        else
            return response()
            ->json([
                    'status' => 'fail',
                    'status_code' =>  422
            ]);
    }

    //s3 signed upload request
    public function upload_request(Request $request){
        $bucket      = env('S3_USER_UPLOADS');
        $region      = env('S3_REGION');
        $accesskey   = env('S3_RW_ID');
        $secret      = env('S3_RW_PASS');
        $filename    = $request->get('filename');
        $contentType = 'image/jpeg';
        /*$policy = json_encode(array(
            'expiration' => date('Y-m-d\TG:i:s\Z', strtotime('+6 hours')),
            'conditions' => array(
                array('bucket' => $bucket),
                array('key' => $filename),
                array('acl' => 'public-read'),
                array('starts-with', '$Content-Type', $contentType),
                array('success_action_status' => '201'),
            ),
        ));*/
        $policy = json_encode(array(
            'Expires' => date('Y-m-d\TG:i:s\Z', strtotime('+6 hours')),
            'conditions' => array(
                array('Bucket' => $bucket),
                array('Key' => $filename),
                array('ACL' => 'public-read'),
                array('starts-with', '$Content-Type', $contentType),
                array('success_action_status' => '201'),
            ),
        ));

        $base64Policy = base64_encode($policy);
        $signature    = base64_encode(hash_hmac("sha1", $base64Policy, $secret, $raw_output = true));
        
        $s3 = Storage::disk('s3');
        $client = $s3->getDriver()->getAdapter()->getClient();
        $expiry = "+6 hours";
        $command = $client->getCommand('PutObject', [
            'Bucket' => $bucket,
            'Key'    => $filename,
            'ACL' => 'public-read',
            'ContentType' => 'image/jpeg',
            'Expires' => 600000
        ]);
                
        $request = $client->createPresignedRequest($command, $expiry);

        return response()->json([
            'endpoint_url' => (string) $request->getUri()
        ]);
    }

    public function update(Request $request, $id){
        $currentUser = $_SESSION['user'];
        $image = Image::find($id);

        if(!$image)
            abort(404, 'Image not found.');
        //$image->fill($request->all());
        $image->description = $request->get('description');
        $image->location = $request->get('location');
        $image->lat = !empty($request->get('lat')) ? $request->get('lat') : $image->lat;
        $image->lon = !empty($request->get('lon')) ? $request->get('lon') : $image->lon;
        if($image->save())
            return response()
            ->json([
                'status' => 'ok',
                'message' => 'Image details updated.',
                'status_code' =>  200,
            ]);
        else
            return response()
            ->json([
                    'status' => 'fail',
                    'status_code' =>  422
            ]);  
    }

    //remove image
    public function remove(Request $request, $id){
        $currentUser = $_SESSION['user'];
        $image = Image::find($id);
        if(!$image)
            abort(404, 'Image not found.');

        if($image->delete())
            return response()
            ->json([
                'status' => 'ok',
                'message' => 'Image deleted.',
                'status_code' =>  200
            ]);
        else
            return response()
            ->json([
                    'status' => 'fail',
                    'message' => 'Unable to delete image.',
                    'status_code' =>  422
            ]); 
    }

    //upload file to s3
    private function uploadFileToS3(ImageRequest $request)
    {
        $currentUser = $_SESSION['user'];
        $image = $request->file('image');
        $imageFileName = time() . '.' . $image->getClientOriginalExtension();
        $s3 = \Storage::disk('s3');
        $s3->put($imageFileName, file_get_contents($image), 'public');
        $bucket      = env('S3_USER_UPLOADS');
        $url =  "https://".$bucket.".s3.amazonaws.com/".$imageFileName;
        return $url;
    }

    //get image information
    public function getImageInfo(Request $request, $id){
        $currentUser = $_SESSION['user'];
        $image = Image::find($id);
        $image->mine = false;
        if($image->user_id==$currentUser->id)
            $image->mine = true;
        if(!$image)
            abort(404, 'Image not found.');
    
        return $image;
    }

    //get all images
    public function getAllImages(Request $request){
        $image = Image::get();
        return $image;
    }
}