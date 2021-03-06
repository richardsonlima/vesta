<?php
// Init
error_reporting(NULL);
ob_start();
session_start();

$TAB = 'DNS';
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Header
include($_SERVER['DOCUMENT_ROOT'].'/templates/header.html');

// Panel
top_panel($user,$TAB);

// Check user argument?
if (empty($_GET['domain'])) {
    header("Location: /list/dns/");
    exit;
}

// Edit as someone else?
if (($_SESSION['user'] == 'admin') && (!empty($_GET['user']))) {
    $user=escapeshellarg($_GET['user']);
}

// Check domain
if ((!empty($_GET['domain'])) && (empty($_GET['record_id'])))  {
    $v_domain = escapeshellarg($_GET['domain']);
    exec (VESTA_CMD."v-list-dns-domain ".$user." ".$v_domain." json", $output, $return_var);
    check_return_code($return_var,$output);
    if (empty($_SESSION['error_msg'])) {
        $data = json_decode(implode('', $output), true);
        unset($output);
        $v_username = $user;
        $v_domain = $_GET['domain'];
        $v_ip = $data[$v_domain]['IP'];
        $v_template = $data[$v_domain]['TPL'];
        $v_ttl = $data[$v_domain]['TTL'];
        $v_exp = $data[$v_domain]['EXP'];
        $v_soa = $data[$v_domain]['SOA'];
        $v_date = $data[$v_domain]['DATE'];
        $v_time = $data[$v_domain]['TIME'];
        $v_suspended = $data[$v_domain]['SUSPENDED'];
        if ( $v_suspended == 'yes' ) {
            $v_status =  'suspended';
        } else {
            $v_status =  'active';
        }
        exec (VESTA_CMD."v-list-dns-templates json", $output, $return_var);
        $templates = json_decode(implode('', $output), true);
        unset($output);
    }

    // Action
    if (!empty($_POST['save'])) {
        $v_domain = escapeshellarg($_POST['v_domain']);

        // IP
        if (($v_ip != $_POST['v_ip']) && (empty($_SESSION['error_msg']))) {
            $v_ip = escapeshellarg($_POST['v_ip']);
            exec (VESTA_CMD."v-change-dns-domain-ip ".$v_username." ".$v_domain." ".$v_ip." 'no'", $output, $return_var);
            check_return_code($return_var,$output);
            $restart_dns = 'yes';
            unset($output);
        }

        // Template
        if (( $_SESSION['user'] == 'admin') && ($v_template != $_POST['v_template']) && (empty($_SESSION['error_msg']))) {
            $v_template = escapeshellarg($_POST['v_template']);
            exec (VESTA_CMD."v-change-dns-domain-tpl ".$v_username." ".$v_domain." ".$v_template." 'no'", $output, $return_var);
            check_return_code($return_var,$output);
            unset($output);
            $restart_dns = 'yes';
        }

        // SOA
        if (($v_soa != $_POST['v_soa']) && (empty($_SESSION['error_msg']))) {
            $v_soa = escapeshellarg($_POST['v_soa']);
            exec (VESTA_CMD."v-change-dns-domain-soa ".$v_username." ".$v_domain." ".$v_soa." 'no'", $output, $return_var);
            check_return_code($return_var,$output);
            unset($output);
            $restart_dns = 'yes';
        }

        // EXP
        if (($v_exp != $_POST['v_exp']) && (empty($_SESSION['error_msg']))) {
            $v_exp = escapeshellarg($_POST['v_exp']);
            exec (VESTA_CMD."v-change-dns-domain-exp ".$v_username." ".$v_domain." ".$v_exp." 'no'", $output, $return_var);
            check_return_code($return_var,$output);
            unset($output);
            $restart_dns = 'yes';
        }

        // TTL
        if (($v_ttl != $_POST['v_ttl']) && (empty($_SESSION['error_msg']))) {
            $v_ttl = escapeshellarg($_POST['v_ttl']);
            exec (VESTA_CMD."v-change-dns-domain-ttl ".$v_username." ".$v_domain." ".$v_ttl." 'no'", $output, $return_var);
            check_return_code($return_var,$output);
            unset($output);
            $restart_dns = 'yes';
        }

        // Restart dns
        if (!empty($restart_dns) && (empty($_SESSION['error_msg']))) {
            exec (VESTA_CMD."v-restart-dns", $output, $return_var);
            check_return_code($return_var,$output);
            unset($output);
        }

        if (empty($_SESSION['error_msg'])) {
            $_SESSION['ok_msg'] = __('Changes has been saved.');
        }
    }

    if ($_SESSION['user'] == 'admin') {
        include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/edit_dns.html');
    } else {
        include($_SERVER['DOCUMENT_ROOT'].'/templates/user/edit_dns.html');
    }
    unset($_SESSION['error_msg']);
    unset($_SESSION['ok_msg']);
} else {
    $v_domain = escapeshellarg($_GET['domain']);
    $v_record_id = escapeshellarg($_GET['record_id']);
    exec (VESTA_CMD."v-list-dns-records ".$user." ".$v_domain." 'json'", $output, $return_var);
    check_return_code($return_var,$output);
    if (empty($_SESSION['error_msg'])) {
        $data = json_decode(implode('', $output), true);
        unset($output);
        $v_username = $user;
        $v_domain = $_GET['domain'];
        $v_record_id = $_GET['record_id'];
        $v_rec = $data[$v_record_id]['RECORD'];
        $v_type = $data[$v_record_id]['TYPE'];
        $v_val = $data[$v_record_id]['VALUE'];
        $v_priority = $data[$v_record_id]['PRIORITY'];
        $v_suspended = $data[$v_record_id]['SUSPENDED'];
        if ( $v_suspended == 'yes' ) {
            $v_status =  'suspended';
        } else {
            $v_status =  'active';
        }
        $v_date = $data[$v_record_id]['DATE'];
        $v_time = $data[$v_record_id]['TIME'];
    }

    // Action
    if (!empty($_POST['save'])) {
        $v_domain = escapeshellarg($_POST['v_domain']);
        $v_record_id = escapeshellarg($_POST['v_record_id']);
        if (($v_val != $_POST['v_val']) || ($v_priority != $_POST['v_priority']) && (empty($_SESSION['error_msg']))) {
            $v_val = escapeshellarg($_POST['v_val']);
            $v_priority = escapeshellarg($_POST['v_priority']);
            exec (VESTA_CMD."v-change-dns-record ".$v_username." ".$v_domain." ".$v_record_id." ".$v_val." ".$v_priority, $output, $return_var);
            check_return_code($return_var,$output);
            $v_val = $_POST['v_val'];
            $restart_dns = 'yes';
            unset($output);
        }

        if (($_GET['record_id'] != $_POST['v_record_id']) && (empty($_SESSION['error_msg']))) {
            $v_old_record_id = escapeshellarg($_GET['record_id']);
            exec (VESTA_CMD."v-change-dns-record-id ".$v_username." ".$v_domain." ".$v_old_record_id." ".$v_record_id, $output, $return_var);
            check_return_code($return_var,$output);
            unset($output);
        }

        if (empty($_SESSION['error_msg'])) {
            $_SESSION['ok_msg'] = __('Changes has been saved.');
        }

        if ((empty($_SESSION['error_msg'])) && ($_GET['record_id'] != $_POST['v_record_id'])) {
            header("Location: /edit/dns/?domain=".$_GET['domain']."&record_id=".$_POST['v_record_id']);
            exit;
        }
    }

    include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/edit_dns_rec.html');
    unset($_SESSION['error_msg']);
    unset($_SESSION['ok_msg']);
}


// Footer
include($_SERVER['DOCUMENT_ROOT'].'/templates/footer.html');
