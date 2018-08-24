<?php
/**
 * # Filters
 * [ fld, equal ]
 * [ fld, [ in ] ]
 * [ fld, min, max ]
 * [ fld%, like ]
 * [ fld>, lower limit ]
 * [ fld<, upper limit ]
 * [ fld>=, min  ]
 * [ fld<=, max ]
 * [ fld!, not equal ]
 * [ fld!, [not in] ]
 */
namespace Data {
    const NOT_FOUND = -1;
    const SQS_ERROR = -9;

    function query() {
        global $DB;
        try {
            return \call_user_func_array( Array($DB, "query"), func_get_args() );
        }
        catch( Exception $ex ) {
            throw new \Exception( $ex->getMessage(), SQS_ERROR );
        }
    }
    function fetch() {
        $stm = \call_user_func_array( "\\Data\\query", func_get_args() );
        $row = $stm->fetch();
        if( !$row ) throw new \Exception('[Data] There is no data!', NOT_FOUND);
        return $row;
    }
    function exec() {
        global $DB;
        \call_user_func_array( "\\Data\\query", func_get_args() );
        return $DB->lastId();
    }
}
namespace Data\User {
    function name() {
        global $DB;
        return $DB->table('user');
    }
    function all() {
        $stm = \Data\query('SELECT id FROM' . \Data\User\name());
        $ids = [];
        while( null != ($row = $stm->fetch()) ) {
            $ids[] = intVal($row[0]);
        }
        return $ids;
    }
    function get( $id ) {
        $row = \Data\fetch('SELECT * FROM' . \Data\User\name() . 'WHERE id=?', $id );
        return ['id' => intVal($row['id']),
                'dashboard' => $row['dashboard'],
                'login' => $row['login'],
                'password' => $row['password'],
                'name' => $row['name'],
                'roles' => $row['roles'],
                'enabled' => $row['enabled'],
                'creation' => $row['creation'],
                'data' => $row['data']];
    }
    function add( $fields ) {
        return \Data\exec(
            'INSERT INTO' . \Data\User\name() . '(`dashboard`,`login`,`password`,`name`,`roles`,`enabled`,`creation`,`data`)'
          . 'VALUES(?,?,?,?,?,?,?,?)',
            $fields['dashboard'],
            $fields['login'],
            $fields['password'],
            $fields['name'],
            $fields['roles'],
            $fields['enabled'],
            $fields['creation'],
            $fields['data']);
    }
    function upd( $id, $values ) {
        \Data\exec(
            'UPDATE' . \Data\User\name()
          . 'SET `dashboard`=?,,`login`=?,,`password`=?,,`name`=?,,`roles`=?,,`enabled`=?,,`creation`=?,,`data`=? '
          . 'WHERE id=?',
            $id,
            $values['dashboard'],
            $values['login'],
            $values['password'],
            $values['name'],
            $values['roles'],
            $values['enabled'],
            $values['creation'],
            $values['data']);
    }
    function del( $id ) {
        \Data\exec( 'DELETE FROM' . \Data\User\name() . 'WHERE id=?', $id );
    }
    function getOrganizations( $id ) {
        global $DB;
        $stm = \Data\query(
            'SELECT `Organization` FROM' . $DB->table('Organization_User')
          . 'WHERE `User`=?', $id);
        $ids = [];
        while( null != ($row = $stm->fetch()) ) {
            $ids[] = intVal($row[0]);
        }
        return $ids;
    }
    function linkOrganizations( $id, $idOrganization ) {
        global $DB;
        \Data\query(
            'INSERT INTO' . $DB->table('Organization_User')
          . '(`User`, `Organization`)'
          . 'VALUES(?,?)', $id, $idOrganization);
    }
    function unlinkOrganizations( $id, $idOrganization=null ) {
        global $DB;
        if( $idOrganization == null ) {
          \Data\query(
              'DELETE FROM' . $DB->table('Organization_User')
            . 'WHERE `User`=?', $id);
        }
        else {
          \Data\query(
              'DELETE FROM' . $DB->table('Organization_User')
            . 'WHERE `User`=? AND `Organization`=?', $id, $idOrganization);
        }
    }
    function getCarecenters( $id ) {
        global $DB;
        $stm = \Data\query(
            'SELECT `Carecenter` FROM' . $DB->table('Carecenter_User')
          . 'WHERE `User`=?', $id);
        $ids = [];
        while( null != ($row = $stm->fetch()) ) {
            $ids[] = intVal($row[0]);
        }
        return $ids;
    }
    function linkCarecenters( $id, $idCarecenter ) {
        global $DB;
        \Data\query(
            'INSERT INTO' . $DB->table('Carecenter_User')
          . '(`User`, `Carecenter`)'
          . 'VALUES(?,?)', $id, $idCarecenter);
    }
    function unlinkCarecenters( $id, $idCarecenter=null ) {
        global $DB;
        if( $idCarecenter == null ) {
          \Data\query(
              'DELETE FROM' . $DB->table('Carecenter_User')
            . 'WHERE `User`=?', $id);
        }
        else {
          \Data\query(
              'DELETE FROM' . $DB->table('Carecenter_User')
            . 'WHERE `User`=? AND `Carecenter`=?', $id, $idCarecenter);
        }
    }
}
namespace Data\Organization {
    function name() {
        global $DB;
        return $DB->table('organization');
    }
    function all() {
        $stm = \Data\query('SELECT id FROM' . \Data\Organization\name());
        $ids = [];
        while( null != ($row = $stm->fetch()) ) {
            $ids[] = intVal($row[0]);
        }
        return $ids;
    }
    function get( $id ) {
        $row = \Data\fetch('SELECT * FROM' . \Data\Organization\name() . 'WHERE id=?', $id );
        return ['id' => intVal($row['id']),
                'name' => $row['name']];
    }
    function add( $fields ) {
        return \Data\exec(
            'INSERT INTO' . \Data\Organization\name() . '(`name`)'
          . 'VALUES(?)',
            $fields['name']);
    }
    function upd( $id, $values ) {
        \Data\exec(
            'UPDATE' . \Data\Organization\name()
          . 'SET `name`=? '
          . 'WHERE id=?',
            $id,
            $values['name']);
    }
    function del( $id ) {
        \Data\exec( 'DELETE FROM' . \Data\Organization\name() . 'WHERE id=?', $id );
    }
    function getCarecenters( $id ) {
        $stm = \Data\query(
            'SELECT id FROM' . \Data\Carecenter\name()
          . 'WHERE `organization`=?', $id);
        $ids = [];
        while( null != ($row = $stm->fetch()) ) {
            $ids[] = intVal($row[0]);
        }
        return $ids;
    }
    function getStructures( $id ) {
        $stm = \Data\query(
            'SELECT id FROM' . \Data\Structure\name()
          . 'WHERE `organization`=?', $id);
        $ids = [];
        while( null != ($row = $stm->fetch()) ) {
            $ids[] = intVal($row[0]);
        }
        return $ids;
    }
    function getAdmins( $id ) {
        global $DB;
        $stm = \Data\query(
            'SELECT `User` FROM' . $DB->table('Organization_User')
          . 'WHERE `Organization`=?', $id);
        $ids = [];
        while( null != ($row = $stm->fetch()) ) {
            $ids[] = intVal($row[0]);
        }
        return $ids;
    }
    function linkAdmins( $id, $idUser ) {
        global $DB;
        \Data\query(
            'INSERT INTO' . $DB->table('Organization_User')
          . '(`Organization`, `User`)'
          . 'VALUES(?,?)', $id, $idUser);
    }
    function unlinkAdmins( $id, $idUser=null ) {
        global $DB;
        if( $idUser == null ) {
          \Data\query(
              'DELETE FROM' . $DB->table('Organization_User')
            . 'WHERE `Organization`=?', $id);
        }
        else {
          \Data\query(
              'DELETE FROM' . $DB->table('Organization_User')
            . 'WHERE `Organization`=? AND `User`=?', $id, $idUser);
        }
    }
}
namespace Data\Carecenter {
    function name() {
        global $DB;
        return $DB->table('carecenter');
    }
    function all() {
        $stm = \Data\query('SELECT id FROM' . \Data\Carecenter\name());
        $ids = [];
        while( null != ($row = $stm->fetch()) ) {
            $ids[] = intVal($row[0]);
        }
        return $ids;
    }
    function get( $id ) {
        $row = \Data\fetch('SELECT * FROM' . \Data\Carecenter\name() . 'WHERE id=?', $id );
        return ['id' => intVal($row['id']),
                'name' => $row['name']];
    }
    function add( $fields ) {
        return \Data\exec(
            'INSERT INTO' . \Data\Carecenter\name() . '(`name`)'
          . 'VALUES(?)',
            $fields['name']);
    }
    function upd( $id, $values ) {
        \Data\exec(
            'UPDATE' . \Data\Carecenter\name()
          . 'SET `name`=? '
          . 'WHERE id=?',
            $id,
            $values['name']);
    }
    function del( $id ) {
        \Data\exec( 'DELETE FROM' . \Data\Carecenter\name() . 'WHERE id=?', $id );
    }
    function getOrganization( $id ) {
        $row = \Data\fetch(
            'SELECT `organization` FROM' . \Data\Carecenter\name()
          . 'WHERE id=?', $id);
        return intVal($row[0]);
    }
    function getStructure( $id ) {
        $row = \Data\fetch(
            'SELECT `structure` FROM' . \Data\Carecenter\name()
          . 'WHERE id=?', $id);
        return intVal($row[0]);
    }
    function getAdmins( $id ) {
        global $DB;
        $stm = \Data\query(
            'SELECT `User` FROM' . $DB->table('Carecenter_User')
          . 'WHERE `Carecenter`=?', $id);
        $ids = [];
        while( null != ($row = $stm->fetch()) ) {
            $ids[] = intVal($row[0]);
        }
        return $ids;
    }
    function linkAdmins( $id, $idUser ) {
        global $DB;
        \Data\query(
            'INSERT INTO' . $DB->table('Carecenter_User')
          . '(`Carecenter`, `User`)'
          . 'VALUES(?,?)', $id, $idUser);
    }
    function unlinkAdmins( $id, $idUser=null ) {
        global $DB;
        if( $idUser == null ) {
          \Data\query(
              'DELETE FROM' . $DB->table('Carecenter_User')
            . 'WHERE `Carecenter`=?', $id);
        }
        else {
          \Data\query(
              'DELETE FROM' . $DB->table('Carecenter_User')
            . 'WHERE `Carecenter`=? AND `User`=?', $id, $idUser);
        }
    }
}
namespace Data\Structure {
    function name() {
        global $DB;
        return $DB->table('structure');
    }
    function all() {
        $stm = \Data\query('SELECT id FROM' . \Data\Structure\name());
        $ids = [];
        while( null != ($row = $stm->fetch()) ) {
            $ids[] = intVal($row[0]);
        }
        return $ids;
    }
    function get( $id ) {
        $row = \Data\fetch('SELECT * FROM' . \Data\Structure\name() . 'WHERE id=?', $id );
        return ['id' => intVal($row['id']),
                'name' => $row['name'],
                'exams' => $row['exams'],
                'vaccins' => $row['vaccins'],
                'patient' => $row['patient'],
                'forms' => $row['forms'],
                'types' => $row['types']];
    }
    function add( $fields ) {
        return \Data\exec(
            'INSERT INTO' . \Data\Structure\name() . '(`name`,`exams`,`vaccins`,`patient`,`forms`,`types`)'
          . 'VALUES(?,?,?,?,?,?)',
            $fields['name'],
            $fields['exams'],
            $fields['vaccins'],
            $fields['patient'],
            $fields['forms'],
            $fields['types']);
    }
    function upd( $id, $values ) {
        \Data\exec(
            'UPDATE' . \Data\Structure\name()
          . 'SET `name`=?,,`exams`=?,,`vaccins`=?,,`patient`=?,,`forms`=?,,`types`=? '
          . 'WHERE id=?',
            $id,
            $values['name'],
            $values['exams'],
            $values['vaccins'],
            $values['patient'],
            $values['forms'],
            $values['types']);
    }
    function del( $id ) {
        \Data\exec( 'DELETE FROM' . \Data\Structure\name() . 'WHERE id=?', $id );
    }
    function getOrganization( $id ) {
        $row = \Data\fetch(
            'SELECT `organization` FROM' . \Data\Structure\name()
          . 'WHERE id=?', $id);
        return intVal($row[0]);
    }
    function getCarecenters( $id ) {
        $stm = \Data\query(
            'SELECT id FROM' . \Data\Carecenter\name()
          . 'WHERE `structure`=?', $id);
        $ids = [];
        while( null != ($row = $stm->fetch()) ) {
            $ids[] = intVal($row[0]);
        }
        return $ids;
    }
}
namespace Data\Patient {
    function name() {
        global $DB;
        return $DB->table('patient');
    }
    function all() {
        $stm = \Data\query('SELECT id FROM' . \Data\Patient\name());
        $ids = [];
        while( null != ($row = $stm->fetch()) ) {
            $ids[] = intVal($row[0]);
        }
        return $ids;
    }
    function get( $id ) {
        $row = \Data\fetch('SELECT * FROM' . \Data\Patient\name() . 'WHERE id=?', $id );
        return ['id' => intVal($row['id']),
                'key' => $row['key']];
    }
    function add( $fields ) {
        return \Data\exec(
            'INSERT INTO' . \Data\Patient\name() . '(`key`)'
          . 'VALUES(?)',
            $fields['key']);
    }
    function upd( $id, $values ) {
        \Data\exec(
            'UPDATE' . \Data\Patient\name()
          . 'SET `key`=? '
          . 'WHERE id=?',
            $id,
            $values['key']);
    }
    function del( $id ) {
        \Data\exec( 'DELETE FROM' . \Data\Patient\name() . 'WHERE id=?', $id );
    }
    function getAdmissions( $id ) {
        $stm = \Data\query(
            'SELECT id FROM' . \Data\Admission\name()
          . 'WHERE `patient`=?', $id);
        $ids = [];
        while( null != ($row = $stm->fetch()) ) {
            $ids[] = intVal($row[0]);
        }
        return $ids;
    }
    function getAttachments( $id ) {
        $stm = \Data\query(
            'SELECT id FROM' . \Data\Attachment\name()
          . 'WHERE `patient`=?', $id);
        $ids = [];
        while( null != ($row = $stm->fetch()) ) {
            $ids[] = intVal($row[0]);
        }
        return $ids;
    }
    function getVaccins( $id ) {
        $stm = \Data\query(
            'SELECT id FROM' . \Data\Vaccin\name()
          . 'WHERE `patient`=?', $id);
        $ids = [];
        while( null != ($row = $stm->fetch()) ) {
            $ids[] = intVal($row[0]);
        }
        return $ids;
    }
}
namespace Data\PatientField {
    function name() {
        global $DB;
        return $DB->table('patientField');
    }
    function all() {
        $stm = \Data\query('SELECT id FROM' . \Data\PatientField\name());
        $ids = [];
        while( null != ($row = $stm->fetch()) ) {
            $ids[] = intVal($row[0]);
        }
        return $ids;
    }
    function get( $id ) {
        $row = \Data\fetch('SELECT * FROM' . \Data\PatientField\name() . 'WHERE id=?', $id );
        return ['id' => intVal($row['id']),
                'key' => $row['key'],
                'value' => $row['value']];
    }
    function add( $fields ) {
        return \Data\exec(
            'INSERT INTO' . \Data\PatientField\name() . '(`key`,`value`)'
          . 'VALUES(?,?)',
            $fields['key'],
            $fields['value']);
    }
    function upd( $id, $values ) {
        \Data\exec(
            'UPDATE' . \Data\PatientField\name()
          . 'SET `key`=?,,`value`=? '
          . 'WHERE id=?',
            $id,
            $values['key'],
            $values['value']);
    }
    function del( $id ) {
        \Data\exec( 'DELETE FROM' . \Data\PatientField\name() . 'WHERE id=?', $id );
    }
}
namespace Data\File {
    function name() {
        global $DB;
        return $DB->table('file');
    }
    function all() {
        $stm = \Data\query('SELECT id FROM' . \Data\File\name());
        $ids = [];
        while( null != ($row = $stm->fetch()) ) {
            $ids[] = intVal($row[0]);
        }
        return $ids;
    }
    function get( $id ) {
        $row = \Data\fetch('SELECT * FROM' . \Data\File\name() . 'WHERE id=?', $id );
        return ['id' => intVal($row['id']),
                'name' => $row['name'],
                'hash' => $row['hash'],
                'mime' => $row['mime'],
                'size' => $row['size']];
    }
    function add( $fields ) {
        return \Data\exec(
            'INSERT INTO' . \Data\File\name() . '(`name`,`hash`,`mime`,`size`)'
          . 'VALUES(?,?,?,?)',
            $fields['name'],
            $fields['hash'],
            $fields['mime'],
            $fields['size']);
    }
    function upd( $id, $values ) {
        \Data\exec(
            'UPDATE' . \Data\File\name()
          . 'SET `name`=?,,`hash`=?,,`mime`=?,,`size`=? '
          . 'WHERE id=?',
            $id,
            $values['name'],
            $values['hash'],
            $values['mime'],
            $values['size']);
    }
    function del( $id ) {
        \Data\exec( 'DELETE FROM' . \Data\File\name() . 'WHERE id=?', $id );
    }
}
namespace Data\Admission {
    function name() {
        global $DB;
        return $DB->table('admission');
    }
    function all() {
        $stm = \Data\query('SELECT id FROM' . \Data\Admission\name());
        $ids = [];
        while( null != ($row = $stm->fetch()) ) {
            $ids[] = intVal($row[0]);
        }
        return $ids;
    }
    function get( $id ) {
        $row = \Data\fetch('SELECT * FROM' . \Data\Admission\name() . 'WHERE id=?', $id );
        return ['id' => intVal($row['id']),
                'enter' => $row['enter'],
                'exit' => $row['exit']];
    }
    function add( $fields ) {
        return \Data\exec(
            'INSERT INTO' . \Data\Admission\name() . '(`enter`,`exit`)'
          . 'VALUES(?,?)',
            $fields['enter'],
            $fields['exit']);
    }
    function upd( $id, $values ) {
        \Data\exec(
            'UPDATE' . \Data\Admission\name()
          . 'SET `enter`=?,,`exit`=? '
          . 'WHERE id=?',
            $id,
            $values['enter'],
            $values['exit']);
    }
    function del( $id ) {
        \Data\exec( 'DELETE FROM' . \Data\Admission\name() . 'WHERE id=?', $id );
    }
    function getPatient( $id ) {
        $row = \Data\fetch(
            'SELECT `patient` FROM' . \Data\Admission\name()
          . 'WHERE id=?', $id);
        return intVal($row[0]);
    }
    function getConsultations( $id ) {
        $stm = \Data\query(
            'SELECT id FROM' . \Data\Consultation\name()
          . 'WHERE `admission`=?', $id);
        $ids = [];
        while( null != ($row = $stm->fetch()) ) {
            $ids[] = intVal($row[0]);
        }
        return $ids;
    }
}
namespace Data\Consultation {
    function name() {
        global $DB;
        return $DB->table('consultation');
    }
    function all() {
        $stm = \Data\query('SELECT id FROM' . \Data\Consultation\name());
        $ids = [];
        while( null != ($row = $stm->fetch()) ) {
            $ids[] = intVal($row[0]);
        }
        return $ids;
    }
    function get( $id ) {
        $row = \Data\fetch('SELECT * FROM' . \Data\Consultation\name() . 'WHERE id=?', $id );
        return ['id' => intVal($row['id']),
                'date' => $row['date']];
    }
    function add( $fields ) {
        return \Data\exec(
            'INSERT INTO' . \Data\Consultation\name() . '(`date`)'
          . 'VALUES(?)',
            $fields['date']);
    }
    function upd( $id, $values ) {
        \Data\exec(
            'UPDATE' . \Data\Consultation\name()
          . 'SET `date`=? '
          . 'WHERE id=?',
            $id,
            $values['date']);
    }
    function del( $id ) {
        \Data\exec( 'DELETE FROM' . \Data\Consultation\name() . 'WHERE id=?', $id );
    }
    function getAdmission( $id ) {
        $row = \Data\fetch(
            'SELECT `admission` FROM' . \Data\Consultation\name()
          . 'WHERE id=?', $id);
        return intVal($row[0]);
    }
    function getDatas( $id ) {
        $stm = \Data\query(
            'SELECT id FROM' . \Data\Data\name()
          . 'WHERE `consultation`=?', $id);
        $ids = [];
        while( null != ($row = $stm->fetch()) ) {
            $ids[] = intVal($row[0]);
        }
        return $ids;
    }
}
namespace Data\Data {
    function name() {
        global $DB;
        return $DB->table('data');
    }
    function all() {
        $stm = \Data\query('SELECT id FROM' . \Data\Data\name());
        $ids = [];
        while( null != ($row = $stm->fetch()) ) {
            $ids[] = intVal($row[0]);
        }
        return $ids;
    }
    function get( $id ) {
        $row = \Data\fetch('SELECT * FROM' . \Data\Data\name() . 'WHERE id=?', $id );
        return ['id' => intVal($row['id']),
                'key' => $row['key'],
                'value' => $row['value']];
    }
    function add( $fields ) {
        return \Data\exec(
            'INSERT INTO' . \Data\Data\name() . '(`key`,`value`)'
          . 'VALUES(?,?)',
            $fields['key'],
            $fields['value']);
    }
    function upd( $id, $values ) {
        \Data\exec(
            'UPDATE' . \Data\Data\name()
          . 'SET `key`=?,,`value`=? '
          . 'WHERE id=?',
            $id,
            $values['key'],
            $values['value']);
    }
    function del( $id ) {
        \Data\exec( 'DELETE FROM' . \Data\Data\name() . 'WHERE id=?', $id );
    }
    function getConsultation( $id ) {
        $row = \Data\fetch(
            'SELECT `consultation` FROM' . \Data\Data\name()
          . 'WHERE id=?', $id);
        return intVal($row[0]);
    }
}
namespace Data\Shapshot {
    function name() {
        global $DB;
        return $DB->table('shapshot');
    }
    function all() {
        $stm = \Data\query('SELECT id FROM' . \Data\Shapshot\name());
        $ids = [];
        while( null != ($row = $stm->fetch()) ) {
            $ids[] = intVal($row[0]);
        }
        return $ids;
    }
    function get( $id ) {
        $row = \Data\fetch('SELECT * FROM' . \Data\Shapshot\name() . 'WHERE id=?', $id );
        return ['id' => intVal($row['id']),
                'key' => $row['key'],
                'value' => $row['value']];
    }
    function add( $fields ) {
        return \Data\exec(
            'INSERT INTO' . \Data\Shapshot\name() . '(`key`,`value`)'
          . 'VALUES(?,?)',
            $fields['key'],
            $fields['value']);
    }
    function upd( $id, $values ) {
        \Data\exec(
            'UPDATE' . \Data\Shapshot\name()
          . 'SET `key`=?,,`value`=? '
          . 'WHERE id=?',
            $id,
            $values['key'],
            $values['value']);
    }
    function del( $id ) {
        \Data\exec( 'DELETE FROM' . \Data\Shapshot\name() . 'WHERE id=?', $id );
    }
}
namespace Data\Attachment {
    function name() {
        global $DB;
        return $DB->table('attachment');
    }
    function all() {
        $stm = \Data\query('SELECT id FROM' . \Data\Attachment\name());
        $ids = [];
        while( null != ($row = $stm->fetch()) ) {
            $ids[] = intVal($row[0]);
        }
        return $ids;
    }
    function get( $id ) {
        $row = \Data\fetch('SELECT * FROM' . \Data\Attachment\name() . 'WHERE id=?', $id );
        return ['id' => intVal($row['id']),
                'name' => $row['name'],
                'desc' => $row['desc'],
                'date' => $row['date'],
                'mime' => $row['mime']];
    }
    function add( $fields ) {
        return \Data\exec(
            'INSERT INTO' . \Data\Attachment\name() . '(`name`,`desc`,`date`,`mime`)'
          . 'VALUES(?,?,?,?)',
            $fields['name'],
            $fields['desc'],
            $fields['date'],
            $fields['mime']);
    }
    function upd( $id, $values ) {
        \Data\exec(
            'UPDATE' . \Data\Attachment\name()
          . 'SET `name`=?,,`desc`=?,,`date`=?,,`mime`=? '
          . 'WHERE id=?',
            $id,
            $values['name'],
            $values['desc'],
            $values['date'],
            $values['mime']);
    }
    function del( $id ) {
        \Data\exec( 'DELETE FROM' . \Data\Attachment\name() . 'WHERE id=?', $id );
    }
    function getPatient( $id ) {
        $row = \Data\fetch(
            'SELECT `patient` FROM' . \Data\Attachment\name()
          . 'WHERE id=?', $id);
        return intVal($row[0]);
    }
}
namespace Data\Vaccin {
    function name() {
        global $DB;
        return $DB->table('vaccin');
    }
    function all() {
        $stm = \Data\query('SELECT id FROM' . \Data\Vaccin\name());
        $ids = [];
        while( null != ($row = $stm->fetch()) ) {
            $ids[] = intVal($row[0]);
        }
        return $ids;
    }
    function get( $id ) {
        $row = \Data\fetch('SELECT * FROM' . \Data\Vaccin\name() . 'WHERE id=?', $id );
        return ['id' => intVal($row['id']),
                'key' => $row['key'],
                'date' => $row['date'],
                'lot' => $row['lot']];
    }
    function add( $fields ) {
        return \Data\exec(
            'INSERT INTO' . \Data\Vaccin\name() . '(`key`,`date`,`lot`)'
          . 'VALUES(?,?,?)',
            $fields['key'],
            $fields['date'],
            $fields['lot']);
    }
    function upd( $id, $values ) {
        \Data\exec(
            'UPDATE' . \Data\Vaccin\name()
          . 'SET `key`=?,,`date`=?,,`lot`=? '
          . 'WHERE id=?',
            $id,
            $values['key'],
            $values['date'],
            $values['lot']);
    }
    function del( $id ) {
        \Data\exec( 'DELETE FROM' . \Data\Vaccin\name() . 'WHERE id=?', $id );
    }
    function getPatient( $id ) {
        $row = \Data\fetch(
            'SELECT `patient` FROM' . \Data\Vaccin\name()
          . 'WHERE id=?', $id);
        return intVal($row[0]);
    }
}
?>
