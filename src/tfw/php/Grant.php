<?php
// Grantings of the current user.
class Grant {
    private $user;

    function __construct( $userId=0 ) {
        $this->user = new User( $userId );        
    }

    function getId() {
        return $this->user->getId();
    }

    function canRenameOrga( $orgaId ) {
        return $this->isAdminOfOrga( $orgaId );
    }

    function canDeleteOrga( $orgaId ) {
        return $this->isAdminOfOrga( $orgaId );
    }

    
    private function isAdminOfOrga( $orgaId ) {
        $organizationsIds = \Data\User\getOrganizations( $this->getId() );
        if( in_array( $orgaId, $organizationsIds ) ) return true;
        return false;
    }
}
?>
