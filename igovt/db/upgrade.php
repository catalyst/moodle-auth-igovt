<?php

function xmldb_auth_igovt_upgrade($oldversion=0) {

    global $CFG, $THEME, $db;

    $result = true;

    if ($result && $oldversion < 2011091300) {

        /// Define table enrol_igovt_invitations to be created
        $table = new XMLDBTable('auth_igovt_invitations');

        /// Adding fields to table enrol_igovt_invitations
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('userid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('accesscodehash', XMLDB_TYPE_CHAR, '40', null, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('datecreated', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('status', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, null, null, 'issued');
        $table->addFieldInfo('datechanged', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('attempts', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');

        /// Adding keys to table enrol_igovt_invitations
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));

        /// Adding indexes to table enrol_igovt_invitations
        $table->addIndexInfo('accesscodehash_idx', XMLDB_INDEX_UNIQUE, array('userid', 'accesscodehash'));

        /// Launch create table for enrol_igovt_invitations
        $result = $result && create_table($table);
    }


    return $result;
}

?>
