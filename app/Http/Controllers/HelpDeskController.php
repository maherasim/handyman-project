<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HelpDesk;
use Yajra\DataTables\DataTables;
use App\Http\Requests\HelpDeskRequest;
use App\Models\Setting;
use App\Traits\NotificationTrait;
use App\Models\HelpDeskActivityMapping;
use App\Models\HandymanCommissionRequest;

class HelpDeskController extends Controller
{
    use NotificationTrait;
     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $filter = [
            'status' => $request->status,
        ];
        $pageTitle = trans('messages.helpdesk');
        $auth_user = authSession();
        $assets = ['datatable'];
        return view('helpdesk.index', compact('pageTitle','auth_user','assets','filter'));
    }


    public function index_data(DataTables $datatable,Request $request)
    {
        $query = HelpDesk::query();
        $filter = $request->filter;

        if (isset($filter)) {
            if (isset($filter['column_status'])) {
                $query->where('status', $filter['column_status']);
            }
        }
        if (auth()->user()->hasAnyRole(['admin'])) {
            $query->newquery()->withTrashed();
        } else {
            $userId = auth()->id();
            // Handymen see their own tickets AND tickets linked to a commission request about them
            $commissionHelpdeskIds = HandymanCommissionRequest::where('handyman_id', $userId)
                ->whereNotNull('helpdesk_id')
                ->pluck('helpdesk_id');

            $query->where(function($q) use ($userId, $commissionHelpdeskIds) {
                $q->where('employee_id', $userId)
                  ->orWhereIn('id', $commissionHelpdeskIds);
            });
        }
        
        return $datatable->eloquent($query)
        ->addColumn('check', function ($row) {
            return '<input type="checkbox" class="form-check-input select-table-row"  id="datatable-row-'.$row->id.'"  name="datatable_ids[]" value="'.$row->id.'" data-type="helpdesk" onclick="dataTableRowCheck('.$row->id.',this)">';
        })
        ->editColumn('id' , function ($query){
            return '#'. $query->id;
        })
        ->editColumn('name' , function ($query){
            return view('helpdesk.user', compact('query'));
        })
        ->filterColumn('name',function($query,$keyword){
            $query->whereHas('users',function ($q) use($keyword){
                $q->where('display_name','like','%'.$keyword.'%');
            });
        })
        ->orderColumn('name', function ($query, $order) {
            $query->select('help_desk.*')
                  ->join('users as employees', 'employees.id', '=', 'help_desk.employee_id')
                  ->orderBy('employees.display_name', $order);   
        })
        ->editColumn('subject', function($query){
            // if (auth()->user()->can('helpdesk edit')) {
            //     $link =  '<a class="btn-link btn-link-hover" href='.route('helpdesk.create', ['id' => $query->id]).'>'.$query->subject.'</a>';
            // } else {
            //     $link = $query->subject;
            // }
            return $query->subject;
        })
        ->editColumn('datetime' , function ($query){
            $sitesetup = Setting::where('type','site-setup')->where('key', 'site-setup')->first();
            $datetime = json_decode($sitesetup->value);
            $date = date("$datetime->date_format $datetime->time_format", strtotime($query->updated_at->setTimezone(new \DateTimeZone($datetime->time_zone ?? 'UTC'))));
            return $date;
        })
        ->editColumn('mode' , function ($query){
            return ucfirst($query->mode) ?? '-';
        })
        ->editColumn('role' , function ($query){
            $type = optional($query->users)->user_type;
            if (!$type) return '-';
            return __('messages.' . $type);
        })
        ->editColumn('status' , function ($query){
            $status = $query->status;
            if($status == 0){
                $status = '<span class="badge text-success bg-success-subtle">'.'open'.'</span>';
            }else{
                $status = '<span class="badge text-danger bg-danger-subtle">'.'closed'.'</span>';
            }
            return $status;
        })
        ->addColumn('action', function($row){
            return view('helpdesk.action',compact('row'))->render();
        })
        ->addIndexColumn()
        ->rawColumns(['check','subject','action','status'])
            ->toJson();
    }

    /* bulck action method */
    public function bulk_action(Request $request)
    {
        $ids = explode(',', $request->rowIds);

        $actionType = $request->action_type;

        $message = 'Bulk Action Updated';

        switch ($actionType) {
            case 'change-status':
                $HelpDesk = HelpDesk::whereIn('id', $ids)->update(['status' => $request->status]);
                $message = 'Bulk Help Desk Status Updated';
                break;

            case 'delete':
                HelpDesk::whereIn('id', $ids)->delete();
                $message = 'Bulk Help Desk Deleted';
                break;
            
            case 'restore':
                HelpDesk::whereIn('id', $ids)->restore();
                $message = 'Bulk Help Desk Restored';
                break;

            case 'permanently-delete':
                HelpDesk::whereIn('id', $ids)->forceDelete();
                $message = 'Bulk Help Desk Permanently Deleted';
                break;

            default:
                return response()->json(['status' => false, 'message' => 'Action Invalid']);
                break;
        }

        return response()->json(['status' => true, 'message' => $message]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        if (!auth()->user()->can('helpdesk add')) {
            return redirect()->back()->withErrors(trans('messages.demo_permission_denied'));
        }        
        $pageTitle1 = trans('messages.setting');
        $page = 'taxes';
        $id = $request->id;
        $auth_user = authSession();

        $helpdesk = HelpDesk::find($id);
        $pageTitle = trans('messages.update_form_title',['form'=>'']);
        
        if($helpdesk == null){
            $pageTitle = trans('messages.add_button_form',['form' => '']);
            $helpdesk = new HelpDesk;
        }
        
        return view('helpdesk.create', compact('pageTitle' ,'helpdesk' ,'auth_user','pageTitle1','page' ));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(HelpDeskRequest $request)
    {
        if(demoUserPermission()){
            return  redirect()->back()->withErrors(trans('messages.demo_permission_denied'));
        }

        $helpdesk = $request->all();

        $helpdesk['employee_id'] = !empty($request->employee_id) ? $request->employee_id : auth()->user()->id; 
        // $helpdesk['status'] = 'open';
        $result = HelpDesk::updateOrCreate(['id' => $request->id], $helpdesk);  
       
        $activity_data = [
            'activity_type' => 'add_helpdesk',
            'helpdesk_id' => $result->id,
            'sender_id' => $result->employee_id,
            'receiver_id' => admin_id(),
            'helpdesk' => $result,
        ];
        $this->sendNotification($activity_data);

        $this->storeAttachments($request, 'helpdesk_attachment', $result);
        $this->storeAttachments($request, 'helpdesk_activity_attachment', $activity_data);
    
        $message = __('messages.update_form',[ 'form' => __('messages.helpdesk') ] );
		if($result->wasRecentlyCreated){
			$message = __('messages.save_form',[ 'form' => __('messages.helpdesk') ] );
		}
        if($request->is('api/*')) {
            $response = [
                'message'=>$message
            ];
            return comman_custom_response($response);
		}
		return redirect(route('helpdesk.index'))->withSuccess($message);
    }
    
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $auth_user = authSession();
        $helpdeskdata = HelpDesk::with('helpdeskactivity')->where('id',$id)->first();
        if(empty($helpdeskdata))
        {
            $msg = __('messages.not_found_entry',['name' => __('messages.helpdesk')] );
            return redirect(route('helpdesk.index'))->withError($msg);
        }
        $isOwner  = $helpdeskdata->employee_id == auth()->user()->id;
        $isAdmin  = auth()->user()->hasRole(['admin', 'demo_admin']);
        // Handyman involved in a commission request linked to this ticket
        $isLinkedHandyman = HandymanCommissionRequest::where('handyman_id', auth()->id())
            ->where('helpdesk_id', $id)
            ->exists();

        if (!$isOwner && !$isAdmin && !$isLinkedHandyman) {
            return redirect(route('home'))->withErrors(trans('messages.demo_permission_denied'));
        }
       
        $sitesetup = Setting::where('type','site-setup')->where('key', 'site-setup')->first();
        $datetime = $sitesetup ? json_decode($sitesetup->value) : null;
        $pageTitle = trans('messages.query' ) .' '. trans('messages.detail' );
        $assets = ['datatable'];
        
        return view('helpdesk.view', compact('pageTitle','auth_user','assets','helpdeskdata','datetime'));

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if(demoUserPermission()){
            if(request()->is('api/*')){
                return comman_message_response( __('messages.demo_permission_denied') );
            }
            return  redirect()->back()->withErrors(trans('messages.demo_permission_denied'));
        }
        $helpdesk = HelpDesk::find($id);
        $msg= __('messages.msg_fail_to_delete',['item' => __('messages.helpdesk')] );

        if($helpdesk!='') {
            $helpdesk->delete();
            $msg= __('messages.msg_deleted',['name' => __('messages.helpdesk')] );
        }
        if(request()->is('api/*')){
            return comman_custom_response(['message'=> $msg , 'status' => true]);
        }
        return comman_custom_response(['message'=> $msg, 'status' => true]);
    }
    public function action(Request $request){
        $id = $request->id;
        $helpdesk = HelpDesk::withTrashed()->where('id',$id)->first();
        $msg = __('messages.not_found_entry',['name' => __('messages.helpdesk')] );
        if($request->type === 'restore'){
            $helpdesk->restore();
            $msg = __('messages.msg_restored',['name' => __('messages.helpdesk')] );
        }

        if($request->type === 'forcedelete'){
            $helpdesk->forceDelete();
            $msg = __('messages.msg_forcedelete',['name' => __('messages.helpdesk')] );
        }

        return comman_custom_response(['message'=> $msg , 'status' => true]);
    }
    public function closed(Request $request, $id){
        
        $helpdesk = HelpDesk::where('id', $id)->first();

        if ($helpdesk && $helpdesk->status == 0) {
            $helpdesk->update(['status' => 1]);
            if(auth()->user()->hasRole(['admin', 'demo_Admin'])) {
                $receiver_id = $helpdesk->employee_id;
            } else {
                $receiver_id = admin_id();
            }
            $activity_data = [
                'activity_type' => 'closed_helpdesk',
                'helpdesk_id' => $helpdesk->id,
                'sender_id' => auth()->user()->id,
                'receiver_id' => $receiver_id,
                'helpdesk' => $helpdesk
            ];
            $this->sendNotification($activity_data);
            $message = __('messages.closed_successfully', [ 'id' => $helpdesk->id ]);
            if(request()->is('api/*')){
                return comman_custom_response(['message'=> $message , 'status' => true]);
            }
            return redirect()->route('helpdesk.index')->withSuccess($message);
        }elseif ($helpdesk && $helpdesk->status == 1){
            $message = __('messages.already_closed_successfully', [ 'id' => $helpdesk->id ]);
            if(request()->is('api/*')){
                return comman_custom_response(['message'=> $message , 'status' => true]);
            }
            return redirect()->route('helpdesk.index')->withSuccess($message);
        }
        if(request()->is('api/*')){
            $message = __('messages.record_not_found');
            return comman_custom_response(['message'=> $message , 'status' => true]);
        }
        return redirect()->route('helpdesk.index')->withError(__('messages.record_not_found'));

    }
    public function activity(Request $request,$id){

        $helpdesk = HelpDesk::where('id', $id)->first();

        if ($helpdesk && $helpdesk->status == 0) {
            $senderId  = auth()->user()->id;
            $isAdmin   = auth()->user()->hasRole(['admin', 'demo_Admin']);
            $message   = $request->description ?? null;

            // Determine all recipients for this helpdesk thread
            // Check if this is a commission request ticket with a linked handyman
            $commissionRequest = HandymanCommissionRequest::where('helpdesk_id', $helpdesk->id)->first();
            $linkedHandymanId  = $commissionRequest ? $commissionRequest->handyman_id : null;

            if ($isAdmin) {
                // Admin → notify provider (employee_id) and handyman (if linked)
                $recipients = array_filter(array_unique([
                    $helpdesk->employee_id,
                    $linkedHandymanId,
                ]), fn($r) => $r && $r !== $senderId);
            } elseif ($linkedHandymanId && $senderId == $linkedHandymanId) {
                // Handyman → notify admin
                $recipients = [admin_id()];
            } else {
                // Provider or anyone else → notify admin
                $recipients = [admin_id()];
            }

            // Save one activity row per recipient and send notifications
            $firstActivity = null;
            foreach ($recipients as $receiverId) {
                $activity = HelpDeskActivityMapping::updateOrCreate(
                    [
                        'helpdesk_id' => $helpdesk->id,
                        'sender_id'   => $senderId,
                        'receiver_id' => $receiverId,
                        'messages'    => $message,
                    ],
                    [
                        'helpdesk_id'   => $helpdesk->id,
                        'sender_id'     => $senderId,
                        'receiver_id'   => $receiverId,
                        'messages'      => $message,
                        'activity_type' => 'reply_helpdesk',
                    ]
                );
                if (!$firstActivity) $firstActivity = $activity;

                $this->sendNotification([
                    'activity_type' => 'reply_helpdesk',
                    'helpdesk_id'   => $helpdesk->id,
                    'sender_id'     => $senderId,
                    'receiver_id'   => $receiverId,
                    'messages'      => $message,
                    'helpdesk'      => $helpdesk,
                ]);
            }

            $activity = $firstActivity ?? HelpDeskActivityMapping::create([
                'helpdesk_id'   => $helpdesk->id,
                'sender_id'     => $senderId,
                'receiver_id'   => admin_id(),
                'messages'      => $message,
                'activity_type' => 'reply_helpdesk',
            ]);

            $this->storeAttachments($request, 'helpdesk_activity_attachment', $activity);
            
            $message = __('messages.message_successfully_send' );
            if(request()->is('api/*')){
                return comman_custom_response(['message'=> $message , 'status' => true]);
            }
            return redirect()->route('helpdesk.show', $helpdesk->id)->withSuccess($message);
        }
        if(request()->is('api/*')){
            $message = __('messages.record_not_found');
            return comman_custom_response(['message'=> $message , 'status' => true]);
        }
        return redirect()->route('helpdesk.index')->withError(__('messages.record_not_found'));

    }
    private function storeAttachments($request, $attachmentPrefix, $data)
    {
   
        $file = [];
        $temporaryFiles = [];

        if ($request->is('api/*')) {
            if ($request->has('attachment_count')) {
                for ($i = 0; $i < $request->attachment_count; $i++) {
                    $attachment = "{$attachmentPrefix}_{$i}";
                    if ($request->hasFile($attachment)) {
                        $uploadedFile = $request->file($attachment);
                        if ($uploadedFile && $uploadedFile->isValid()) {
                            if ($uploadedFile->getSize() > 4 * 1024 * 1024) {
                                continue;
                            }

                            $file[] = $uploadedFile;
                        }
                    } elseif ($request->$attachment != null) {
                        $file[] = $request->$attachment;
                    }
                }
                $file = $this->prepareHelpdeskAttachmentsForStorage($file, $temporaryFiles);
                if (!empty($file)) {
                    storeMediaFile($data, $file, $attachmentPrefix);
                }
            }
        } else {

            if ($request->hasFile($attachmentPrefix)) {
                $uploadedFiles = $request->file($attachmentPrefix);
                $file = is_array($uploadedFiles) ? $uploadedFiles : [$uploadedFiles];
                $file = array_values(array_filter($file, function ($uploadedFile) {
                    return $uploadedFile && $uploadedFile->isValid() && $uploadedFile->getSize() <= 4 * 1024 * 1024;
                }));
                $file = $this->prepareHelpdeskAttachmentsForStorage($file, $temporaryFiles);
                if (!empty($file)) {
                    storeMediaFile($data, $file, $attachmentPrefix);
                }
            }	
        }

        foreach ($temporaryFiles as $temporaryFile) {
            if (is_string($temporaryFile) && file_exists($temporaryFile)) {
                @unlink($temporaryFile);
            }
        }
    }

    private function prepareHelpdeskAttachmentsForStorage(array $files, array &$temporaryFiles): array
    {
        $batchSize = count($files);

        return array_values(array_filter(array_map(function ($file) use (&$temporaryFiles, $batchSize) {
            return $this->prepareHelpdeskAttachmentForStorage($file, $temporaryFiles, $batchSize);
        }, $files)));
    }

    private function prepareHelpdeskAttachmentForStorage($file, array &$temporaryFiles, int $batchSize = 1)
    {
        if (!$file instanceof \Illuminate\Http\UploadedFile || !$file->isValid()) {
            return $file;
        }

        $mimeType = (string) $file->getMimeType();
        if (strpos($mimeType, 'image/') !== 0) {
            return $file;
        }

        $sourcePath = $file->getRealPath();
        if (!$sourcePath || !file_exists($sourcePath)) {
            return $file;
        }

        try {
            $size = @getimagesize($sourcePath);
            if (!$size || empty($size[0]) || empty($size[1])) {
                return $file;
            }

            [$width, $height] = $size;
            $isLargeBatch = $batchSize >= 3;
            $maxDimension = $isLargeBatch ? 800 : 1000;
            $maxPreparedSize = $isLargeBatch ? 120 * 1024 : 250 * 1024;
            $fileSize = (int) $file->getSize();

            if (!$isLargeBatch && $fileSize <= $maxPreparedSize && max($width, $height) <= $maxDimension) {
                return $file;
            }

            $sourceImage = $this->createHelpdeskImageResource($sourcePath, $mimeType);
            if (!$sourceImage) {
                return $file;
            }

            $ratio = min($maxDimension / $width, $maxDimension / $height, 1);
            $newWidth = max(1, (int) round($width * $ratio));
            $newHeight = max(1, (int) round($height * $ratio));
            $newImage = imagecreatetruecolor($newWidth, $newHeight);
            $background = imagecolorallocate($newImage, 255, 255, 255);
            imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $background);
            imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

            $temporaryDirectory = storage_path('app/tmp/helpdesk-uploads');
            if (!is_dir($temporaryDirectory)) {
                mkdir($temporaryDirectory, 0755, true);
            }

            $temporaryPath = $temporaryDirectory . DIRECTORY_SEPARATOR . uniqid('helpdesk_', true) . '.jpg';
            $qualities = $isLargeBatch ? [62, 52, 44, 36] : [72, 62, 52, 44];

            foreach ($qualities as $quality) {
                imagejpeg($newImage, $temporaryPath, $quality);
                if (file_exists($temporaryPath) && filesize($temporaryPath) <= $maxPreparedSize) {
                    break;
                }
            }

            imagedestroy($sourceImage);
            imagedestroy($newImage);

            if (file_exists($temporaryPath) && filesize($temporaryPath) > 0) {
                $temporaryFiles[] = $temporaryPath;
                return $temporaryPath;
            }
        } catch (\Throwable $e) {
            \Log::warning('Helpdesk attachment resize skipped: ' . $e->getMessage());
        }

        return $file;
    }

    private function createHelpdeskImageResource(string $path, string $mimeType)
    {
        switch ($mimeType) {
            case 'image/jpeg':
            case 'image/jpg':
                return imagecreatefromjpeg($path);
            case 'image/png':
                return imagecreatefrompng($path);
            case 'image/gif':
                return imagecreatefromgif($path);
            case 'image/webp':
                return function_exists('imagecreatefromwebp') ? imagecreatefromwebp($path) : null;
            default:
                return null;
        }
    }
    
}
