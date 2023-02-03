<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Volunteer;
use App\Models\Delegation;

use Illuminate\Http\Request;

class UsersController extends Controller
{
    public function avatarChangeForm()
    {
        return view('dashboard.changeAvatarForm');
    }

    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'file' => 'required|max:5120|image',
        ]);
        $filename = Auth::user()->nameVol . Auth::user()->surnameVol . Auth::user()->surname2Vol . substr(Auth::user()->numDocVol, 0, 4) . '.jpg';
        $request->file('file')->storeAs('avatar', $filename);
        $volunteer = Volunteer::find(Auth::user()->id)
            ->update(['imageVol' => $filename]);
        session()->flash('successUploadImage', 'Se ha subido el Avatar.');
        return redirect()->route('dashboard.changeAvatar');
    }

    public function showAllUsers()
    {
        $volunteers = Volunteer::select(
            'id',
            'nameVol',
            'surnameVol',
            'surname2Vol',
            'birthDateVol',
            'typeDocVol',
            'numDocVol',
            'telVol',
            'sexVol',
            'shirtSizeVol',
            'persMailVol',
            'corpMailVol',
            'typeViaVol',
            'direcVol',
            'numVol',
            'flatVol',
            'aditiInfoVol',
            'codPosVol',
            'stateVol',
            'townVol',
            'imageVol',
            'organiVol',
            /********/
            'isLoggeable',
            'isInternVol',
            'isRegisterComplete',
            /********/
            'nameAuthVol',
            'tlfAuthVol',
            'numDocAuthVol'
        )
            ->where('isAdminVol', '!=', "1")
            ->get();
        return view("dashboard.showAllUsers", compact("volunteers"));
    }

    public function showMyProfile()
    {
        $volunteer = Volunteer::select(
            'id',
            'nameVol',
            'surnameVol',
            'surname2Vol',
            'birthDateVol',
            'typeDocVol',
            'numDocVol',
            'telVol',
            'sexVol',
            'shirtSizeVol',
            'persMailVol',
            'corpMailVol',
            'typeViaVol',
            'direcVol',
            'numVol',
            'flatVol',
            'aditiInfoVol',
            'codPosVol',
            'stateVol',
            'townVol',
            'imageVol',
            'organiVol',
            /********/
            'isInternVol',
            /********/
            'nameAuthVol',
            'tlfAuthVol',
            'numDocAuthVol'
        )
            ->where('id', Auth::user()->id)
            ->first();

        $allDelegations = Delegation::all();
        return view('dashboard.showMyProfileForm', compact("volunteer", "allDelegations"));

    }

    public function banUser(Request $request)
    {
        $volunteer = Volunteer::select('id')
            ->where('id', $request['id'])
            ->update(['isLoggeable' => 0]);
        session()->flash('successUser', 'Se ha BLOQUEADO el USUARIO.');
        return redirect()->route('dashboard.showAllUsers');

    }

    public function unbanUser(Request $request)
    {
        $volunteer = Volunteer::select('id')
            ->where('id', $request['id'])
            ->update(['isLoggeable' => 1]);
        session()->flash('successUser', 'Se ha DESBLOQUEADO el USUARIO.');
        return redirect()->route('dashboard.showAllUsers');
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'shirtSizeVol' => 'required',
            'organiVol' => 'required',
            'telVol' => 'required',
            'typeViaVol' => 'required',
            'direcVol' => 'required',
            'numVol' => 'required',
            'aditiInfoVol' => 'required',
            'codPosVol' => 'required',
            'stateVol' => 'required',
            'townVol' => 'required',
            // 'delegations' => 'required',
        ]);

        $volunteer = Volunteer::find(Auth::user()->id);
        if ($volunteer->delegations()->count() != 0) {
            $volunteer->delegations()->detach();
        }

        $volunteer->delegations()->attach($request->delegations);

        Auth::user()->update(
            [
                'shirtSizeVol' => $request->shirtSizeVol,
                'organiVol' => $request->organiVol,
                'telVol' => $request->telVol,
                'corpMailVol' => $request->corpMailVol,
                'typeViaVol' => $request->typeViaVol,
                'direcVol' => $request->direcVol,
                'numVol' => $request->numVol,
                'flatVol' => $request->flatVol,
                'aditiInfoVol' => $request->aditiInfoVol,
                'codPosVol' => $request->codPosVol,
                'stateVol' => $request->stateVol,
                'townVol' => $request->townVol
            ],
        );

        session()->flash('successUpdateUser', 'Se ha Actualizado el USUARIO.');
        return redirect()->route('dashboard.showMyProfile');
    }

    public static function showEachInterest($activity)
    {
        $interest = [];
        foreach ($activity as $getTypeAct) {
            foreach ($getTypeAct->typeAct as $typeAct) {
                array_push($interest, $typeAct->nameTypeAct);
            }
        }
        return array_unique($interest); 
    }

    /* Método de búsqueda de usuarios según su nombre pepe*/ 

    public function searchVolunteerByNameSurname($search){

        $volunteers=Volunteer::where('nameVol', $search)->get();

        return view('dashboard.showAllUsers', compact("volunteers"));
        
    }


    /* pepe */

    public function search(Request $request){
    
        if($request->ajax()){
    
            $data=Volunteer::where('id','like','%'.$request->search.'%')
            ->orwhere('nameVol','like','%'.$request->search.'%')
            ->orwhere('surnameVol','like','%'.$request->search.'%')->get();
    
            $output='';
        if(count($data)>0){

                $output ='

                ';

                foreach($data as $row){

                $output .='

                    <div class="row" style="baseDashboard.css">
                    
                    <div>';

                        if ($row->imageVol == 0 || $row == null){
                            $output .= '<img src="asset("images/dashboard/noProfileImage.jpg");> alt="No hay imagen" class="avatarInShowAllUsers">';
                        }                          
                        else{
                            $output .= '<img src=" asset("storage/avatar/" .'. $row->imageVol.') " alt="'.$row->nameVol.'"
                                class="avatarInShowAllUsers">';
                        } 

                $output .= '
                    </div>

                        <div>
                            <strong>
                                '.$row->id.''.
                                 $row->nameVol.''.
                                 $row->surname2Vol.' 
                            </strong>
                            <br />';

                if ($row->organiVol == false)
                {
                    $output .='
                        SIN Empresa Asociada';
                }
                                
                else
                {
                    $output .=''.$row->organiVol.'';
                }

                    $output .='
                        </div>

                        <div class="mailVol">
                            <a href="mailto:'. $row->persMailVol . '.">' . $row->persMailVol . '</a>
                            '; 
                if ($row->corpMailVol)
                {
                    $output .= '
                        (C)
                        <a href="mailto:'. $row->corpMailVol .'">'.$row->corpMailVol .'</a>';
                }

                    $output .='
                        </div>

                        <div class="tlfVol">
                            <a href="tel:+34 ' . $row->telVol .'">'. $row->telVol . '.</a>
                        </div>
                        <div class="controlButton moreDetails">
                        <i class="bx bxs-down-arrow"></i>
                        </div>
                    </div>';

                   $output .= ' 
                   
                    <div class="hidden">
                    <div class="eachRow">
                        <div>
                            <strong>Fecha de nacimiento: </strong>
                            {{ date("d-m-Y", strtotime('.$row->birthDateVol.')) }}
                        </div>
                        <div>
                            <strong>{{ '.$row->typeDocVol.' }}: </strong>
                            {{ '.$row->numDocVol.' }}
                        </div>
                        <div>
                            <strong>Sexo:</strong>
                            {{ '.$row->sexVol .'}}
                        </div>
                        <div>
                            <strong>Talla de camiseta: </strong>
                            {{'. $row->shirtSizeVol .' }}
                        </div>
                    </div>
                    <div class="eachRow">
                        <div>
                            <strong>Delegaciones: </strong>';
                            if (count($row->delegations) == 0){
                                $output .= 'No tiene delegación';
                            }else{
                                foreach ($row->delegations as $delegation){
                                    $output .=''. $delegation->nameDel . ''; 
                                }
                                    
                            }
                            $output .= '
                        </div>
                    </div>
                    <div class="eachRow">
                        <div>
                            <strong>Dirección: </strong> <br />
                            <div>
                                {{ '.$row->typeViaVol .'}}
                                {{ '.$row->direcVol .'}}
                            </div>
                            <div>
                                <strong>Nº: </strong>
                                {{'. $row->numVol .'}}
                                {{'. $row->flatVol .' }}
                            </div>
                            <div>
                                <strong>Código Postal: </strong>
                                {{ '. $row->codPosVol .'}}
                            </div>
                            <div>
                                <strong>Provincia: </strong>
                                {{'. $row->stateVol .' }}
                            </div>
                            <div>
                                <strong>Ciudad: </strong>
                                {{ '.$row->townVol .' }}
                            </div>
                            <div>
                                <strong>Información Adicional: </strong>
                                {{ '.$row->aditiInfoVol .'}}
                            </div>

                        </div>
                        <div>
                            <strong>Educación: </strong><br />';

                            if (count($row->education) == 0){
                                $output .= 'No tiene titulación registrada';
                            }else{
                                foreach ($row->education as $education){
                                   $output .= ' '.$education->titleEdu .'
                                    <form method="POST" action="{{ route("dashboard.downloadThatEducation") }}"
                                        accept-charset="UTF-8" enctype="multipart/form-data">
                                        @csrf
                                        <input type="hidden" name="id" value="{{ $education->education_id }}">
                                        <p><button type="submit" id="downloadEdu" class="botonesControl"><i
                                                    class="bx bx-save"></i></button></p>
                                    </form>';
                                }
                            }

                        $output .= '
                        </div>
                        <div>
                            <strong>Documentos: </strong> <br />';

                            if (count($row->documents) == 0){
                                $output .= 'No tiene titulación registrada';
                            }else{
                                foreach ($row->documents as $document)
                                $output .= '
                                    {{ $document->titleDoc }}
                                    <form method="POST" action="{{ route("dashboard.showDocument") }}">
                                        @csrf
                                        <input type="hidden" name="doc" value="{{ $document->doc_id }}">
                                        <button type="submit" {{--id="showDocDoc"--}} class="botonesControl"><i
                                                class="bx bx-save"></i></button>
                                    </form>';
                            }
                                
                            $output .= '

                        </div>
                    </div>';

                    if (date("Y") - date("Y", strtotime($row->birthDateVol)) <= 17){
                       
                       $output .= ' <div class="eachRow">
                            <div>
                                <span class="redMark">ES MENOR</span>
                                <div>
                                    <strong>Autorizador:</strong>
                                    {{ '.$row->nameAuthVol .' }}
                                </div>
                                <div>
                                    <strong>Documento de identidad del autorizador:</strong>
                                    {{ '.$row->numDocAuthVol .'}}
                                </div>
                                <div>
                                    <strong>Teléfono del autorizador:</strong>
                                    <a href="tel:+34{{ '.$row->tlfAuthVol .'}}">{{'. $row->tlfAuthVol .'}}</a>
                                </div>
                            </div>
                        </div>';
                    }

                    $output .='

                    <div class="eachRow">
                        <div>
                            <div class="eachRow">
                                <div><strong>Intereses:</strong></div>
                            </div>
                            <div class="eachRow">';

                                if (count(UsersController::showEachInterest($row->activities)) == 0){
                                    $output .= '<div>Aun no tenemos suficientes datos para mostrar intereses 
                                    </div>';
                                }else{
                                    $output .= '
                                    <div>';
                                        foreach (UsersController::showEachInterest($row->activities) as $typeAct){
                                            $output .= '<p>{{ $typeAct }}</p>
                                        
                                            
                                    </div>';
                                        }
                                }
                                 
                                $output .= '

                            </div>
                        </div>
                    </div>
                    <div class="eachRow">
                        <div>
                            <div class="eachRow">
                                <div><strong>Actividades a las que se ha inscrito:</strong></div>
                            </div>';


                            if (count($row->inscriptions) == 0){
                                $output .= '<div class="eachRowInscription">
                                    <div>No se ha unido a ninguna actividad aun</div>
                                </div>';
                            }else{
                                foreach ($row->inscriptions as $eachInscription){
                                    $output .='
                                    <div class="eachRowInscription">
                                        <div style="width:200px;">
                                            <strong>{{'. $eachInscription->activity->nameAct .'}}</strong>
                                        </div>
                                        <div style="width:100px;">
                                            {{'. $eachInscription->activity->dateAct .'}}
                                        </div>
                                        <div>';
                                            if ($eachInscription->isCompletedIns){
                                                $output .= 'ACEPTADO';
                                            }
                                            elseif(is_null($eachInscription->filenameIns) && is_null($eachInscription->isCompletedIns)){
                                                $output .= 'RECHAZADO';
                                            }
                                            elseif ($eachInscription->filenameIns == null){
                                                $output .='DEBES DE SUBIR EL DOCUMENTO FIRMADO EN LA SECCIÓN DE NOTIFICACIONES';
                                            }
                                            elseif ($eachInscription->filenameIns != null){
                                                $output .= 'PREINSCRIPCIÓN REALIZADA<br />
                                                ESPERANDO VALIDACIÓN DE UN ADMINISTRADOR';
                                            }

                                            $output .='
                                        </div>
                                    </div>';
                                }
                            }
                                
                            $output .= '
                        </div>
                    </div>
                    <div class="eachRow">
                        <div class="controlButton lessDetails">
                            <i class="bx bxs-up-arrow" ></i>
                        </div>
                    </div>
                </div> ';

                }

        }
        else{
    
            $output .='No results';
    
        }
    
        return $output;
    
        }

      }

      

}