<?php
$ROLE = "ORGA";

include_once("./data.php");

function execService() {
    $user = new User();
    
    if( $user->isAdmin() || $user->hasRole("ORGA") ) return getAllOrganizations();
    return getMyOrganizations();
}


function getAllOrganizations( $whereClause="" ) {
    $organizations = [];
    $lastOrgaId = 0;
    $lastOrga = null;

    $sql = "SELECT O.id, O.name, C.id, C.name FROM "
         . \Data\Organization\name() . " AS O, "
         . \Data\Carecenter\name() . " AS C "
         . "WHERE C.organization=O.id" . $whereClause
         . " ORDER BY O.name, C.name";
    $stm = \Data\query( $sql );
    
    while( null != ($row = $stm->fetch()) ) {
        $orgaId = intval( $row['O.id'] );
        $orgaName = $row['O.name'];
        $careId = intval( $row['C.id'] );
        $careName = $row['C.name'];
        if( $orgaId != $lastOrgaId ) {
            $lastOrgaId = $orgaId;
            $lastOrga = [
                'id' => $orgaId,
                'name' => $orgaName,
                'carecenters' => []
            ];
            $organizations[] = $lastOrga;
        }
        $lastOrga['carecenters'][] = [
            'id' => $careId,
            'name' => $careName
        ];
    }
    return $organizations;
}

function getMyOrganizations() {
    $user = new User();
    $ids = \Data\User\getOrganizations( $user->getId() );
    return getAllOrganizations( ' AND O.id IN (' . implode( ',', $ids ) . ')' );
}

?>
