<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;
use App\Jobs\SendResetPasswordJob;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginAPIController extends Controller
{
    public function __construct()
    {
        $this->middleware(['jwt.auth'])->only(['setPassword']);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:7|max:50',
            'forced' => 'required|boolean',
            'password_actual' => 'required_if:forced,0',
        ]);

        DB::beginTransaction();
        try {
            $user = Auth::user();

            if (!$request->forced) {
                if (!Hash::check($request->password_actual, $user->password)) {
                    DB::rollback();
                    return response()->json(['error' => 'Password actual inválido!'], 404);
                }
            }

            if(!$user){
                DB::rollback();
                    return response()->json(['error' => 'User not found!'], 404);
            }

            $password = Hash::make($request->password);
            $user->update(['password' => $password, 'loged_once' => true, 'disbled_login_by_wrong_pass' => false]);
            DB::commit();
            return response()->json(['message' => 'Done'], 200);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    /**
     * Create the user's password and send credentials to the user owner if is using email, or send to Admin if user owner uses codigo_login.
     * @param Request $request
     * @return response
     */
    public function forgotPassword(Request $request)
    {
        $validator = $request->validate(['identifier' => 'required']);

        /** Grab the @identifier to determine witch field was used to login and merge this field into the request*/
        $identifier = $request->identifier;
        $field = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'codigo_login';
        $request->merge([$field => $identifier]);

        DB::beginTransaction();
        try {

            $user = User::where($field, $request->identifier)->first();
            if (empty($user)) {
                DB::rollback();
                return response()->json(['message' => 'User not found'], 404);
            }

            $hash = "P@SS" . uniqid();
            $user->update(['password' => Hash::make($hash), 'disbled_login_by_wrong_pass' => false, 'sent_disabled_login' => false, 'loged_once' => false, 'login_attempts' => 0]);

            if ($field == 'email') {

                SendResetPasswordJob::dispatch($user->email, $user, $hash)->delay(now()->addSeconds(10));
                DB::commit();
                return response()->json(['message' => 'Notification with new password was sent'], 200);

            } elseif ($field == 'codigo_login') {

                // Find the Admin user based on role, to send the new default password, since the user owner is using codigo_login
                // $recipient = User::with(['role'])->whereHas('role', function ($q) {
                //     $q->where('codigo', 1);
                // })->first();
                $recipients_emails = User::with(['role'])->whereHas('role', function ($q) {
                    $q->where('codigo', 1);
                })->pluck('email')->toArray();
                // dd($recipients_emails);
                if(empty($recipients_emails)){

                    DB::rollback();
                    return response()->json(['message' => 'Nenhum Administrador registado no sistema para receber 
                    as novas credenciaisi. Por favor consulte a euipa de suporte!'], 404);

                }else{

                    foreach($recipients_emails as $recipient_email) {
                        if(!empty($recipient_email)) {
                            if(filter_var($recipient_email, FILTER_VALIDATE_EMAIL)) {
                               SendResetPasswordJob::dispatch($recipient_email, $user, $hash)->delay(now()->addSeconds(10));
                            }
                            
                        }
                        
                    }

                    DB::commit();
                    return response()->json(['message' => 'Notification with new password was sent'], 200);

                }

            }            
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['message' => $e->getMessage()], 404);
        }

    }
}
