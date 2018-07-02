<?php

namespace Drupal\fossee_stats;

class Utility{

    public static function bootstrap_table_format($headers, $rows) {
        $thead = "";
        $tbody = "";
        foreach ($headers as $header) {
            $thead .= "<th>{$header}</th>";
        }
        foreach ($rows as $row) {
            $tbody .= "<tr>";
            $i = 0;
            foreach ($row as $data) {
                $datalable = $headers[$i++];
                $tbody .= "<td data-label=" . $datalable . ">{$data}</td>";
            }
            $tbody .= "</tr>";
        }
        $table = "

                    <table class='table table-bordered table-hover'>
                      <thead>{$thead}</thead>
                      <tbody>{$tbody}</tbody>
                    </table>

                ";
        return $table;
    }

    public static function events_images_path() {
        return $_SERVER['DOCUMENT_ROOT'] . base_path() . 'events_images/';
    }

    public static function posters_path() {
      return $_SERVER['DOCUMENT_ROOT'] . base_path() . 'campaign_posters/';
    }

    public static function get_file_size_MB($filepath) {
        return round((filesize($filepath)/1024)/1024, 2);
    }

    public static function delete_directory($dirname) {
        if (is_dir($dirname))
            $dir_handle = opendir($dirname);
        if (!$dir_handle)
            return FALSE;
        while ($file = readdir($dir_handle)) {
            if ($file != "." && $file != "..") {
                if (!is_dir($dirname . "/" . $file))
                    unlink($dirname . "/" . $file);
                else
                    delete_directory($dirname . '/' . $file);
            }
        }
        closedir($dir_handle);
        rmdir($dirname);
        return TRUE;
    }

    public static function get_first_dropdown_options_foss_name() {
        $connection = \Drupal::database();
        $query = $connection->select('foss_type');
        $query->fields('foss_type', array(
            'id'
        ));
        $query->fields('foss_type', array(
            'foss_name'
        ));
        $result = $query->execute();
        $options = array();
        while ($foss_detail = $result->fetchObject()) {
            $options[$foss_detail->foss_name] = $foss_detail->foss_name;
        }
        $options["Others"] = "Others";
        return $options;
    }
}