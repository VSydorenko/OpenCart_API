<?php
class ControllerApioptionProduct extends Controller
{
    public function option_value_add()
    {
        $this
            ->load
            ->language('api/option_value_add');
        $json = array();
        $vozvrat_json = array();
        $image_f = file_get_contents('php://input');
        $nameZip = DIR_CACHE . $this
            ->request
            ->get['nameZip'] . '.zip';

        file_put_contents($nameZip, $image_f);

        $zipArc = zip_open($nameZip);

        if (is_resource($zipArc))
        {
            $languages = $this->getLanguages();
            $first_option_value = true;
            $sql_option_value = "INSERT INTO `" . DB_PREFIX . "option_value` (`option_value_id`, `option_id`, `image`, `sort_order`) VALUES ";
            $first_option_value_description = true;
            $sql_option_value_description = "INSERT INTO `" . DB_PREFIX . "option_value_description` (`option_value_id`, `language_id`, `option_id`,`name`) VALUES ";
            $sql = "Select max(`option_value_id`) as `option_value_id` from `" . DB_PREFIX . "option_value`;";
            $result = $this
                ->db
                ->query($sql);
            $option_value_id_max = 0;

            foreach ($result->rows as $row)
            {
                $option_value_id_max = (int)$row['option_value_id'];
            }

            $option_id = 0;

            while ($zip_entry = zip_read($zipArc))
            {
                if (zip_entry_open($zipArc, $zip_entry, "r"))
                {
                    $dump = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));

                    $options_array = json_decode($dump);

                    foreach ($options_array as $option)
                    {
                        $option_id = $option->{'option_id'};
                        $option_value_id = $option->{'option_value_id'};
                        $names = (array)$option->{'name'};
                        $image = $option->{'image'};
                        $sort_order = $option->{'sort_order'};

                        if ($option_value_id == 0)
                        {
                            $option_value_id_max = $option_value_id_max + 1;
                            $option_value_id = $option_value_id_max;
                            $insert = 1;
                        }
                        else
                        {
                            $insert = 0;
                        };

                        $sql_option_value .= ($first_option_value) ? "" : ",";
                        $sql_option_value .= " ( $option_value_id, $option_id, '$image', $sort_order ) ";
                        $first_option_value = false;

                        foreach ($languages as $language)
                        {
                            $language_code = $language['code'];
                            $language_id = $language['language_id'];
                            $name = isset($names[$language_code]) ? urldecode($this
                                ->db
                                ->escape($names[$language_code])) : '';
                            $sql_option_value_description .= ($first_option_value_description) ? "" : ",";
                            $sql_option_value_description .= " ( $option_value_id, $language_id, $option_id, '$name') ";
                            $first_option_value_description = false;
                        }

                        $vozvrat_json[$option->{'ref'}] = $option_value_id;
                    }
                }
            }

            if (!$first_option_value)
            {
                $sql_option_value .= " ON DUPLICATE KEY UPDATE  ";
                $sql_option_value .= "`option_id`= VALUES(`option_id`),";
                $sql_option_value .= "`image`= VALUES(`image`),";
                $sql_option_value .= "`sort_order`= VALUES(`sort_order`)";
                $sql_option_value .= ";";
                $this
                    ->db
                    ->query($sql_option_value);
            }

            if (!$first_option_value_description)
            {
                $sql_option_value_description .= " ON DUPLICATE KEY UPDATE  ";
                $sql_option_value_description .= "`option_id`= VALUES(`option_id`),";
                $sql_option_value_description .= "`name`= VALUES(`name`)";
                $sql_option_value_description .= ";";
                $this
                    ->db
                    ->query($sql_option_value_description);
            }

            zip_close($zipArc);
            unlink($nameZip);
            $json['success'] = $vozvrat_json;
        }
        else
        {
            $json['error'] = 'zip error option add';
        }

        $this
            ->response
            ->addHeader('Content-Type: application/json');
        $this
            ->response
            ->setOutput(json_encode($json));
    }

    public function option_add()
    {
        $this
            ->load
            ->language('api/option_add');
        $json = array();
        $vozvrat_json = array();
        $image_f = file_get_contents('php://input');
        $nameZip = DIR_CACHE . $this
            ->request
            ->get['nameZip'] . '.zip';

        file_put_contents($nameZip, $image_f);

        $zipArc = zip_open($nameZip);

        if (is_resource($zipArc))
        {
            $languages = $this->getLanguages();
            $first_option = true;
            $sql_option = "INSERT INTO `" . DB_PREFIX . "option` (`option_id`, `type`, `sort_order`) VALUES ";
            $first_option_description = true;
            $sql_option_description = "INSERT INTO `" . DB_PREFIX . "option_description` (`option_id`, `language_id`, `name`) VALUES ";
            $sql = "Select max(`option_id`) as `option_id` from `" . DB_PREFIX . "option`;";
            $result = $this
                ->db
                ->query($sql);
            $option_id_max = 0;

            foreach ($result->rows as $row)
            {
                $option_id_max = (int)$row['option_id'];
            }

            $option_id = 0;

            while ($zip_entry = zip_read($zipArc))
            {
                if (zip_entry_open($zipArc, $zip_entry, "r"))
                {
                    $dump = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));

                    $options_array = json_decode($dump);

                    foreach ($options_array as $option)
                    {
                        $option_id = $option->{'option_id'};
                        $names = (array)$option->{'name'};
                        $sort_order = $option->{'sort_order'};
                        $option_type = 'select';

                        if ($option_id == 0)
                        {
                            $option_id_max = $option_id_max + 1;
                            $option_id = $option_id_max;
                            $insert = 1;
                        }
                        else
                        {
                            $insert = 0;
                        };

                        $sql_option .= ($first_option) ? "" : ",";
                        $sql_option .= " ( $option_id, '$option_type', $sort_order ) ";
                        $first_option = false;

                        foreach ($languages as $language)
                        {
                            $language_code = $language['code'];
                            $language_id = $language['language_id'];
                            $name = isset($names[$language_code]) ? urldecode($this
                                ->db
                                ->escape($names[$language_code])) : '';
                            $sql_option_description .= ($first_option_description) ? "" : ",";
                            $sql_option_description .= " ( $option_id, $language_id, '$name') ";
                            $first_option_description = false;
                        }

                        $vozvrat_json[$option->{'ref'}] = $option_id;
                    }
                }
            }

            if (!$first_option)
            {
                $sql_option .= " ON DUPLICATE KEY UPDATE  ";
                $sql_option .= "`type`= VALUES(`type`),";
                $sql_option .= "`sort_order`= VALUES(`sort_order`)";
                $sql_option .= ";";
                $this
                    ->db
                    ->query($sql_option);
            }

            if (!$first_option_description)
            {
                $sql_option_description .= " ON DUPLICATE KEY UPDATE  ";
                $sql_option_description .= "`name`= VALUES(`name`)";
                $sql_option_description .= ";";
                $this
                    ->db
                    ->query($sql_option_description);
            }

            zip_close($zipArc);
            unlink($nameZip);
            $json['success'] = $vozvrat_json;
        }
        else
        {
            $json['error'] = 'zip error option add';
        }

        $this
            ->response
            ->addHeader('Content-Type: application/json');
        $this
            ->response
            ->setOutput(json_encode($json));
    }

    public function deleteOptions()
    {
        $this
            ->load
            ->language('api/deleteOptions');
        $json = array();
        $sql = "TRUNCATE TABLE `" . DB_PREFIX . "option_value`;";
        $this
            ->db
            ->query($sql);
        $sql = "TRUNCATE TABLE `" . DB_PREFIX . "option_value_description`;";
        $this
            ->db
            ->query($sql);
        $sql = "TRUNCATE TABLE `" . DB_PREFIX . "product_option`;";
        $this
            ->db
            ->query($sql);
        $sql = "TRUNCATE TABLE `" . DB_PREFIX . "product_option_value`;";
        $this
            ->db
            ->query($sql);
        $json['success'] = 'Options have been deleted';
        $this
            ->response
            ->addHeader('Content-Type: application/json');
        $this
            ->response
            ->setOutput(json_encode($json));
    }

    public function get_option()
    {
        $this
            ->load
            ->language('api/get_option');
        $json = array();
        $result = null;
        $languages = $this->getLanguages();
        $language_id_u = (int)$this
            ->request
            ->post['param'];

        foreach ($languages as $language)
        {
            $language_code = $language['code'];
            $language_id = $language['language_id'];

            if ($language_code == $language_id_u)
            {
                $sql = "SELECT oc_option.option_id,oc_option_desc.name FROM `" . DB_PREFIX . "option` as oc_option ";
                $sql .= " inner JOIN `" . DB_PREFIX . "option_description` as oc_option_desc on oc_option_desc.option_id = oc_option.option_id and oc_option_desc.language_id = $language_id ;";

                $query = $this
                    ->db
                    ->query($sql);

                $result = $query->rows;
            }
        }

        $json['success'] = $result;
        $this
            ->response
            ->addHeader('Content-Type: application/json');
        $this
            ->response
            ->setOutput(json_encode($json));
    }

    protected function getLanguages()
    {
        $query = $this
            ->db
            ->query("SELECT * FROM `" . DB_PREFIX . "language` WHERE `status`=1 ORDER BY `code`");
        return $query->rows;
    }
}

