<?php namespace Dreamlands\ViewModel;

use Dreamlands\Entity\UserEntity;
use Hashids\Hashids;

class UserViewModel extends AbstractViewModel
{
    /**
     * @var UserEntity
     */
    private $userEntity;
    /**
     * @var Hashids
     */
    private $hashids;

    public function __construct(UserEntity $userEntity, Hashids $hashids)
    {
        $this->userEntity = $userEntity;
        $this->hashids = $hashids;
    }

    public function toDataObject()
    {
        return (object)[
            'id' => $this->hashids->encode($this->userEntity->id),
            'display_name' => $this->userEntity->getDisplayName(),
        ];
    }
}
