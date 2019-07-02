<?php

// This file is part of the Certificate module for Moodle - http://moodle.org/
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
 * Certificate module internal API,
 * this is in separate file to reduce memory use on non-certificate pages.
 *
 * @package    mod_certificate
 * @copyright  Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

//require_once($CFG->dirroot.'/mod/certificate/lib.php');
//require_once($CFG->dirroot.'/course/lib.php');
//require_once($CFG->dirroot.'/grade/lib.php');
//require_once($CFG->dirroot.'/grade/querylib.php');

/** The border image folder */
define('CERT_IMAGE_BORDER', 'borders');
/** The watermark image folder */
define('CERT_IMAGE_WATERMARK', 'watermarks');
/** The signature image folder */
define('CERT_IMAGE_SIGNATURE', 'signatures');
/** The seal image folder */
define('CERT_IMAGE_SEAL', 'seals');

/** Set CERT_PER_PAGE to 0 if you wish to display all certificates on the report page */
define('CERT_PER_PAGE', 30);

define('CERT_MAX_PER_PAGE', 200);


/**
 * Alerts teachers by email of received certificates. First checks
 * whether the option to email teachers is set for this certificate.
 *
 * @param stdClass $course
 * @param stdClass $certificate
 * @param stdClass $certrecord
 * @param stdClass $cm course module
 */
function certificate_email_teachers($course, $certificate, $certrecord, $cm) {
    global $USER, $CFG, $DB;

    if ($certificate->emailteachers == 0) {          // No need to do anything
        return;
    }

    $user = $DB->get_record('user', array('id' => $certrecord->userid));

    if ($teachers = certificate_get_teachers($certificate, $user, $course, $cm)) {
        $strawarded = get_string('awarded', 'certificate');
        foreach ($teachers as $teacher) {
            $info = new stdClass;
            $info->student = fullname($USER);
            $info->course = format_string($course->fullname,true);
            $info->certificate = format_string($certificate->name,true);
            $info->url = $CFG->wwwroot.'/mod/certificate/report.php?id='.$cm->id;
            $from = $USER;
            $postsubject = $strawarded . ': ' . $info->student . ' -> ' . $certificate->name;
            $posttext = certificate_email_teachers_text($info);
            $posthtml = ($teacher->mailformat == 1) ? certificate_email_teachers_html($info) : '';

            @email_to_user($teacher, $from, $postsubject, $posttext, $posthtml);  // If it fails, oh well, too bad.
        }
    }
}

/**
 * Alerts others by email of received certificates. First checks
 * whether the option to email others is set for this certificate.
 * Uses the email_teachers info.
 * Code suggested by Eloy Lafuente
 *
 * @param stdClass $course
 * @param stdClass $certificate
 * @param stdClass $certrecord
 * @param stdClass $cm course module
 */
function certificate_email_others($course, $certificate, $certrecord, $cm) {
    global $USER, $CFG;

    if ($certificate->emailothers) {
        $others = explode(',', $certificate->emailothers);
        if ($others) {
            $strawarded = get_string('awarded', 'certificate');
            foreach ($others as $other) {
                $other = trim($other);
                if (validate_email($other)) {
                    $destination = new stdClass;
                    $destination->id = 1;
                    $destination->email = $other;
                    $info = new stdClass;
                    $info->student = fullname($USER);
                    $info->course = format_string($course->fullname, true);
                    $info->certificate = format_string($certificate->name, true);
                    $info->url = $CFG->wwwroot.'/mod/certificate/report.php?id='.$cm->id;
                    $from = $USER;
                    $postsubject = $strawarded . ': ' . $info->student . ' -> ' . $certificate->name;
                    $posttext = certificate_email_teachers_text($info);
                    $posthtml = certificate_email_teachers_html($info);

                    @email_to_user($destination, $from, $postsubject, $posttext, $posthtml);  // If it fails, oh well, too bad.
                }
            }
        }
    }
}

/**
 * Creates the text content for emails to teachers -- needs to be finished with cron
 *
 * @param $info object The info used by the 'emailteachermail' language string
 * @return string
 */
function certificate_email_teachers_text($info) {
    $posttext = get_string('emailteachermail', 'certificate', $info) . "\n";

    return $posttext;
}

/**
 * Creates the html content for emails to teachers
 *
 * @param $info object The info used by the 'emailteachermailhtml' language string
 * @return string
 */
function certificate_email_teachers_html($info) {
    $posthtml  = '<font face="sans-serif">';
    $posthtml .= '<p>' . get_string('emailteachermailhtml', 'certificate', $info) . '</p>';
    $posthtml .= '</font>';

    return $posthtml;
}

/**
 * Sends the student their issued certificate from moddata as an email
 * attachment.
 *
 * @param stdClass $course
 * @param stdClass $certificate
 * @param stdClass $certrecord
 * @param stdClass $context
 * @param string $filecontents the PDF file contents
 * @param string $filename
 * @return bool Returns true if mail was sent OK and false if there was an error.
 */
function certificate_email_student($course, $certificate, $certrecord, $context, $filecontents, $filename) {
    global $USER;

    // Get teachers
    if ($users = get_users_by_capability($context, 'moodle/course:update', 'u.*', 'u.id ASC',
        '', '', '', '', false, true)) {
        $users = sort_by_roleassignment_authority($users, $context);
        $teacher = array_shift($users);
    }

    // If we haven't found a teacher yet, look for a non-editing teacher in this course.
    if (empty($teacher) && $users = get_users_by_capability($context, 'moodle/course:update', 'u.*', 'u.id ASC',
            '', '', '', '', false, true)) {
        $users = sort_by_roleassignment_authority($users, $context);
        $teacher = array_shift($users);
    }

    // Ok, no teachers, use administrator name
    if (empty($teacher)) {
        $teacher = fullname(get_admin());
    }

    $info = new stdClass;
    $info->username = fullname($USER);
    $info->certificate = format_string($certificate->name, true);
    $info->course = format_string($course->fullname, true);
    $from = fullname($teacher);
    $subject = $info->course . ': ' . $info->certificate;
    $message = get_string('emailstudenttext', 'certificate', $info) . "\n";

    // Make the HTML version more XHTML happy  (&amp;)
    $messagehtml = text_to_html(get_string('emailstudenttext', 'certificate', $info));

    $tempdir = make_temp_directory('certificate/attachment');
    if (!$tempdir) {
        return false;
    }

    $tempfile = $tempdir.'/'.md5(sesskey().microtime().$USER->id.'.pdf');
    $fp = fopen($tempfile, 'w+');
    fputs($fp, $filecontents);
    fclose($fp);

    $prevabort = ignore_user_abort(true);
    $result = email_to_user($USER, $from, $subject, $message, $messagehtml, $tempfile, $filename);
    @unlink($tempfile);
    ignore_user_abort($prevabort);

    return $result;
}

/**
 * This function returns success or failure of file save
 *
 * @param string $pdf is the string contents of the pdf
 * @param int $certrecordid the certificate issue record id
 * @param string $filename pdf filename
 * @param int $contextid context id
 * @return bool return true if successful, false otherwise
 */
function certificate_save_pdf($pdf, $certrecordid, $filename, $contextid) {
    global $USER;

    if (empty($certrecordid)) {
        return false;
    }

    if (empty($pdf)) {
        return false;
    }

    $fs = get_file_storage();

    // Prepare file record object
    $component = 'mod_certificate';
    $filearea = 'issue';
    $filepath = '/';
    $fileinfo = array(
        'contextid' => $contextid,   // ID of context
        'component' => $component,   // usually = table name
        'filearea'  => $filearea,     // usually = table name
        'itemid'    => $certrecordid,  // usually = ID of row in table
        'filepath'  => $filepath,     // any path beginning and ending in /
        'filename'  => $filename,    // any filename
        'mimetype'  => 'application/pdf',    // any filename
        'userid'    => $USER->id);

    // We do not know the previous file name, better delete everything here,
    // luckily there is supposed to be always only one certificate here.
    $fs->delete_area_files($contextid, $component, $filearea, $certrecordid);

    $fs->create_file_from_string($fileinfo, $pdf);

    return true;
}

/**
 * Produces a list of links to the issued certificates.  Used for report.
 *
 * @param stdClass $certificate
 * @param int $userid
 * @param int $contextid
 * @return string return the user files
 */
function certificate_print_user_files($certificate, $userid, $contextid) {
    global $CFG, $DB, $OUTPUT;

    $output = '';

    $certrecord = $DB->get_record('certificate_issues', array('userid' => $userid, 'certificateid' => $certificate->id));
    $fs = get_file_storage();

    $component = 'mod_certificate';
    $filearea = 'issue';
    $files = $fs->get_area_files($contextid, $component, $filearea, $certrecord->id);
    foreach ($files as $file) {
        $filename = $file->get_filename();
        $link = file_encode_url($CFG->wwwroot.'/pluginfile.php', '/'.$contextid.'/mod_certificate/issue/'.$certrecord->id.'/'.$filename);

        $output = '<img src="'.$OUTPUT->pix_url(file_mimetype_icon($file->get_mimetype())).'" height="16" width="16" alt="'.$file->get_mimetype().'" />&nbsp;'.
            '<a href="'.$link.'" >'.s($filename).'</a>';

    }
    $output .= '<br />';
    $output = '<div class="files">'.$output.'</div>';

    return $output;
}


/**
 * Get certificate types indexed and sorted by name for mod_form.
 *
 * @return array containing the certificate type
 */
function certificate_types() {
    $types = array();
    $names = get_list_of_plugins('mod/certificate/type');
    $sm = get_string_manager();
    foreach ($names as $name) {
        if ($sm->string_exists('type'.$name, 'certificate')) {
            $types[$name] = get_string('type'.$name, 'certificate');
        } else {
            $types[$name] = ucfirst($name);
        }
    }
    asort($types);
    return $types;
}

/**
 * Get images for mod_form.
 *
 * @param string $type the image type
 * @return array
 */
function certificate_get_images($type) {
    global $CFG;

    switch($type) {
        case CERT_IMAGE_BORDER :
            $path = "$CFG->dirroot/mod/certificate/pix/borders";
            $uploadpath = "$CFG->dataroot/mod/certificate/pix/borders";
            break;
        case CERT_IMAGE_SEAL :
            $path = "$CFG->dirroot/mod/certificate/pix/seals";
            $uploadpath = "$CFG->dataroot/mod/certificate/pix/seals";
            break;
        case CERT_IMAGE_SIGNATURE :
            $path = "$CFG->dirroot/mod/certificate/pix/signatures";
            $uploadpath = "$CFG->dataroot/mod/certificate/pix/signatures";
            break;
        case CERT_IMAGE_WATERMARK :
            $path = "$CFG->dirroot/mod/certificate/pix/watermarks";
            $uploadpath = "$CFG->dataroot/mod/certificate/pix/watermarks";
            break;
    }
    // If valid path
    if (!empty($path)) {
        $options = array();
        $options += certificate_scan_image_dir($path);
        $options += certificate_scan_image_dir($uploadpath);

        // Sort images
        ksort($options);

        // Add the 'no' option to the top of the array
        $options = array_merge(array('0' => get_string('no')), $options);

        return $options;
    } else {
        return array();
    }
}

/**
 * Sends text to output given the following params.
 *
 * @param stdClass $pdf
 * @param int $x horizontal position
 * @param int $y vertical position
 * @param char $align L=left, C=center, R=right
 * @param string $font any available font in font directory
 * @param char $style ''=normal, B=bold, I=italic, U=underline
 * @param int $size font size in points
 * @param string $text the text to print
 * @param int $width horizontal dimension of text block
 */
function certificate_print_text($pdf, $x, $y, $align, $font='freeserif', $style, $size = 10, $text, $width = 0) {
    $pdf->setFont($font, $style, $size);
    $pdf->SetXY($x, $y);
    $pdf->writeHTMLCell($width, 0, '', '', $text, 0, 0, 0, true, $align);
}

/**
 * Creates rectangles for line border for A4 size paper.
 *
 * @param stdClass $pdf
 * @param stdClass $certificate
 */
function certificate_draw_frame($pdf, $certificate) {
    if ($certificate->bordercolor > 0) {
        if ($certificate->bordercolor == 1) {
            $color = array(0, 0, 0); // black
        }
        if ($certificate->bordercolor == 2) {
            $color = array(153, 102, 51); // brown
        }
        if ($certificate->bordercolor == 3) {
            $color = array(0, 51, 204); // blue
        }
        if ($certificate->bordercolor == 4) {
            $color = array(0, 180, 0); // green
        }
        switch ($certificate->orientation) {
            case 'L':
                // create outer line border in selected color
                $pdf->SetLineStyle(array('width' => 1.5, 'color' => $color));
                $pdf->Rect(10, 10, 277, 190);
                // create middle line border in selected color
                $pdf->SetLineStyle(array('width' => 0.2, 'color' => $color));
                $pdf->Rect(13, 13, 271, 184);
                // create inner line border in selected color
                $pdf->SetLineStyle(array('width' => 1.0, 'color' => $color));
                $pdf->Rect(16, 16, 265, 178);
                break;
            case 'P':
                // create outer line border in selected color
                $pdf->SetLineStyle(array('width' => 1.5, 'color' => $color));
                $pdf->Rect(10, 10, 190, 277);
                // create middle line border in selected color
                $pdf->SetLineStyle(array('width' => 0.2, 'color' => $color));
                $pdf->Rect(13, 13, 184, 271);
                // create inner line border in selected color
                $pdf->SetLineStyle(array('width' => 1.0, 'color' => $color));
                $pdf->Rect(16, 16, 178, 265);
                break;
        }
    }
}

/**
 * Creates rectangles for line border for letter size paper.
 *
 * @param stdClass $pdf
 * @param stdClass $certificate
 */
function certificate_draw_frame_letter($pdf, $certificate) {
    if ($certificate->bordercolor > 0) {
        if ($certificate->bordercolor == 1)    {
            $color = array(0, 0, 0); //black
        }
        if ($certificate->bordercolor == 2)    {
            $color = array(153, 102, 51); //brown
        }
        if ($certificate->bordercolor == 3)    {
            $color = array(0, 51, 204); //blue
        }
        if ($certificate->bordercolor == 4)    {
            $color = array(0, 180, 0); //green
        }
        switch ($certificate->orientation) {
            case 'L':
                // create outer line border in selected color
                $pdf->SetLineStyle(array('width' => 4.25, 'color' => $color));
                $pdf->Rect(28, 28, 736, 556);
                // create middle line border in selected color
                $pdf->SetLineStyle(array('width' => 0.2, 'color' => $color));
                $pdf->Rect(37, 37, 718, 538);
                // create inner line border in selected color
                $pdf->SetLineStyle(array('width' => 2.8, 'color' => $color));
                $pdf->Rect(46, 46, 700, 520);
                break;
            case 'P':
                // create outer line border in selected color
                $pdf->SetLineStyle(array('width' => 1.5, 'color' => $color));
                $pdf->Rect(25, 20, 561, 751);
                // create middle line border in selected color
                $pdf->SetLineStyle(array('width' => 0.2, 'color' => $color));
                $pdf->Rect(40, 35, 531, 721);
                // create inner line border in selected color
                $pdf->SetLineStyle(array('width' => 1.0, 'color' => $color));
                $pdf->Rect(51, 46, 509, 699);
                break;
        }
    }
}

/**
 * Prints border images from the borders folder in PNG or JPG formats.
 *
 * @param stdClass $pdf
 * @param stdClass $certificate
 * @param string $type the type of image
 * @param int $x x position
 * @param int $y y position
 * @param int $w the width
 * @param int $h the height
 */
function certificate_print_image($pdf, $certificate, $type, $x, $y, $w, $h) {
    global $CFG;

    switch($type) {
        case CERT_IMAGE_BORDER :
            $attr = 'borderstyle';
            $path = "$CFG->dirroot/local/rcyci/certificate/pix/$type/$certificate->borderstyle";
            $uploadpath = "$CFG->dataroot/local/rcyci/certificate/pix/$type/$certificate->borderstyle";
            break;
        case CERT_IMAGE_SEAL :
            $attr = 'printseal';
            $path = "$CFG->dirroot/local/rcyci/certificate/pix/$type/$certificate->printseal";
            $uploadpath = "$CFG->dataroot/local/rcyci/certificate/pix/$type/$certificate->printseal";
            break;
        case CERT_IMAGE_SIGNATURE :
            $attr = 'printsignature';
            $path = "$CFG->dirroot/local/rcyci/certificate/pix/$type/$certificate->printsignature";
            $uploadpath = "$CFG->dataroot/local/rcyci/certificate/pix/$type/$certificate->printsignature";
            break;
        case CERT_IMAGE_WATERMARK :
            $attr = 'printwmark';
            $path = "$CFG->dirroot/local/rcyci/certificate/pix/$type/$certificate->printwmark";
            $uploadpath = "$CFG->dataroot/local/rcyci/certificate/pix/$type/$certificate->printwmark";
            break;
    }
    // Has to be valid
    if (!empty($path)) {
        switch ($certificate->$attr) {
            case '0' :
            case '' :
                break;
            default :
                if (file_exists($path)) {
                    $pdf->Image($path, $x, $y, $w, $h);
                }
                if (file_exists($uploadpath)) {
                    $pdf->Image($uploadpath, $x, $y, $w, $h);
                }
                break;
        }
    }
}

/**
 * Generates a 10-digit code of random letters and numbers.
 *
 * @return string
 */
function certificate_generate_code() {
    global $DB;

    $uniquecodefound = false;
    $code = random_string(10);
    while (!$uniquecodefound) {
        if (!$DB->record_exists('certificate_issues', array('code' => $code))) {
            $uniquecodefound = true;
        } else {
            $code = random_string(10);
        }
    }

    return $code;
}

/**
 * Scans directory for valid images
 *
 * @param string the path
 * @return array
 */
function certificate_scan_image_dir($path) {
    // Array to store the images
    $options = array();

    // Start to scan directory
    if (is_dir($path)) {
        $iterator = new DirectoryIterator($path);
        foreach ($iterator as $fileinfo) {
            $filename = $fileinfo->getFilename();
            $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            if ($fileinfo->isFile() && in_array($extension, array('png', 'jpg', 'jpeg'))) {
                $options[$filename] = pathinfo($filename, PATHINFO_FILENAME);
            }
        }
    }
    return $options;
}
