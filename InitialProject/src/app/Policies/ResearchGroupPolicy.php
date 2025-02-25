<?php

namespace App\Policies;

use App\Models\ResearchGroup;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ResearchGroupPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ResearchGroup  $researchGroup
     * @return mixed
     */
    public function view(User $user, ResearchGroup $researchGroup)
    {
        
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        //return true;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ResearchGroup  $researchGroup
     * @return mixed
     */
    public function update(User $user, ResearchGroup $researchGroup)
    {
        // 1) ถ้าเป็น admin หรือ staff => true ทันที
        if ($user->hasRole('admin') || $user->hasRole('staff')) {
            return true;
        }
    
        // 2) ถ้าไม่ใช่ admin/staff => เช็คใน pivot ว่า role == 1 หรือ can_edit == 1
        $pivot = $researchGroup->user()->where('user_id', $user->id)->first();
        if ($pivot) {
            // role == 1 หรือ can_edit == 1 => อนุญาตให้ update
            if ($pivot->pivot->role == 1 || $pivot->pivot->can_edit == 1) {
                return true;
            }
        }
    
        // 3) ไม่เข้าเงื่อนไขใด ๆ => false
        return false;
    }
    

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ResearchGroup  $researchGroup
     * @return mixed
     */
    public function delete(User $user, ResearchGroup $researchGroup)
    {
        $researchGroup=ResearchGroup::find($researchGroup->id)->user()->where('user_id',$user->id)->get();
        //$researchProject = User::with(['researchProject'])->where('id',$user->id)->get();
        
        
        if($user->hasRole('admin')){
            return true;
        }
        foreach ($researchGroup as $res) {
            //print($res);
            if($user->id == $res->id and $res->pivot->role == '1' ){
                return true;
            }
            else{
                return false;
            }
        }
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ResearchGroup  $researchGroup
     * @return mixed
     */
    public function restore(User $user, ResearchGroup $researchGroup)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ResearchGroup  $researchGroup
     * @return mixed
     */
    public function forceDelete(User $user, ResearchGroup $researchGroup)
    {
        //
    }
}
