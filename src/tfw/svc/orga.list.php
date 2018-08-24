<?php
$ROLE = "USER";

include_once("./data.php");

function execService() {
    $user = new User();
    if( $user->isAdmin() || $user->hasRole("ORGA") ) return getAllOrganizations();
    return getMyOrganizations();
}


function getAllOrganizations( $whereClause="" ) {
    error_log("whereClause = $whereClause");

    $organizations = [];
    $lastOrgaId = 0;
    $lastOrga = null;

    $sql = "SELECT id, name FROM "
         . \Data\Organization\name();
    if( $whereClause != '' ) $sql .= " WHERE " . $whereClause;
    $sql .= " ORDER BY name";
    $stm = \Data\query( $sql );

    while( null != ($row = $stm->fetch()) ) {
        $id = intval( $row['id'] );
        $name = $row['name'];
        $organizations[] = [
            'id' => $id, 'name' => $name, 'carecenters' => []
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
