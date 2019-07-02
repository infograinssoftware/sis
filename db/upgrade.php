<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This file keeps track of upgrades to the settings block
 *
 * Sometimes, changes between versions involve alterations to database structures
 * and other major things that may break installations.
 *
 * The upgrade function in this file will attempt to perform all the necessary
 * actions to upgrade your older installation to the current version.
 *
 * If there's something it cannot do itself, it will tell you what you need to do.
 *
 * The commands in here will all be database-neutral, using the methods of
 * database_manager class
 *
 * Please do not forget to use upgrade_set_timeout()
 * before any action that may take longer time to finish.
 *
 * @since Moodle 2.0
 * @package local_sis
 * @copyright 2009 Sam Hemelryk
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * As of the implementation of this block and the general navigation code
 * in Moodle 2.0 the body of immediate upgrade work for this block and
 * settings is done in core upgrade {@see lib/db/upgrade.php}
 *
 * There were several reasons that they were put there and not here, both becuase
 * the process for the two blocks was very similar and because the upgrade process
 * was complex due to us wanting to remvoe the outmoded blocks that this
 * block was going to replace.
 *
 * @param int $oldversion
 * @param object $block
 */
<<<<<<< HEAD
function xmldb_local_sis_upgrade($oldversion, $block) {
=======
 
 function xmldb_local_sis_upgrade($oldversion) {
>>>>>>> 62fad2890f1d8f1375bdbd60832d51a0727f78a7
    global $CFG, $DB;

    $dbman = $DB->get_manager(); //this is new in moodle 3.0
	
    // Put any upgrade step following this.
	$newversion = 2016062202; //put the new version number here
    if ($oldversion < $newversion) {
<<<<<<< HEAD
		//upgrade code starts here//

		  $table = new xmldb_table('si_organization');

        // Adding fields to table si_organization.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('organization', XMLDB_TYPE_CHAR, '225', null, null, null, null);
        $table->add_field('organization_name', XMLDB_TYPE_CHAR, '1000', null, null, null, null);
        $table->add_field('organization_name_a', XMLDB_TYPE_CHAR, '1000', null, null, null, null);
        $table->add_field('organization_type', XMLDB_TYPE_CHAR, '30', null, null, null, null);
        $table->add_field('institute', XMLDB_TYPE_CHAR, '225', null, null, null, null);
        $table->add_field('campus', XMLDB_TYPE_CHAR, '225', null, null, null, null);
        $table->add_field('eff_status', XMLDB_TYPE_CHAR, '1', null, null, null, null);

        // Adding keys to table si_organization.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for si_organization.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
		
		  $table = new xmldb_table('si_organizaiton_section');

        // Adding fields to table si_organizaiton_section.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('organization_id', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('section', XMLDB_TYPE_CHAR, '225', null, null, null, null);
        $table->add_field('section_name', XMLDB_TYPE_CHAR, '1000', null, null, null, null);
        $table->add_field('section_name_a', XMLDB_TYPE_CHAR, '1000', null, null, null, null);
        $table->add_field('section_type', XMLDB_TYPE_CHAR, '225', null, null, null, null);
        $table->add_field('institute', XMLDB_TYPE_CHAR, '225', null, null, null, null);
        $table->add_field('campus', XMLDB_TYPE_CHAR, '225', null, null, null, null);
        $table->add_field('eff_status', XMLDB_TYPE_CHAR, '1', null, null, null, null);

        // Adding keys to table si_organizaiton_section.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for si_organizaiton_section.
=======
		//upgrade code starts here


        // Define table si_lookup to be created.
        $table = new xmldb_table('si_lookup');

        // Adding fields to table si_lookup.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('value', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('category', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('subcategory', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('lang', XMLDB_TYPE_CHAR, '10', null, null, null, null);
        $table->add_field('sort_order', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('institute', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('eff_status', XMLDB_TYPE_CHAR, '1', null, null, null, null);

        // Adding keys to table si_lookup.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for si_lookup.
>>>>>>> 62fad2890f1d8f1375bdbd60832d51a0727f78a7
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

<<<<<<< HEAD
            $table = new xmldb_table('si_institute');

        // Adding fields to table si_institute.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('institute', XMLDB_TYPE_CHAR, '225', null, null, null, null);
=======

        // Define table si_institute to be created.
        $table = new xmldb_table('si_institute');

        // Adding fields to table si_institute.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('institute', XMLDB_TYPE_CHAR, '255', null, null, null, null);
>>>>>>> 62fad2890f1d8f1375bdbd60832d51a0727f78a7
        $table->add_field('institute_name', XMLDB_TYPE_CHAR, '1000', null, null, null, null);
        $table->add_field('institute_name_a', XMLDB_TYPE_CHAR, '1000', null, null, null, null);
        $table->add_field('eff_status', XMLDB_TYPE_CHAR, '1', null, null, null, null);

        // Adding keys to table si_institute.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for si_institute.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
<<<<<<< HEAD
		
		 $table = new xmldb_table('si_campus');

        // Adding fields to table si_campus.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('campus', XMLDB_TYPE_CHAR, '225', null, null, null, null);
        $table->add_field('campus_name', XMLDB_TYPE_CHAR, '1000', null, null, null, null);
        $table->add_field('campus_name_a', XMLDB_TYPE_CHAR, '1000', null, null, null, null);
        $table->add_field('institute', XMLDB_TYPE_CHAR, '225', null, null, null, null);
        $table->add_field('eff_status', XMLDB_TYPE_CHAR, '1', null, null, null, null);

        // Adding keys to table si_campus.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for si_campus.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
		// upgrade code ends here//
        // sis savepoint reached.
        upgrade_plugin_savepoint(true, $newversion, 'local', 'sis');
=======




		// upgrade code ends here
        // tplus savepoint reached.
        upgrade_plugin_savepoint(true, $newversion, 'local', 'tplus');
>>>>>>> 62fad2890f1d8f1375bdbd60832d51a0727f78a7
    }


    return true;
}
