<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Rest extends MY_Controller {

    public function __construct()
    {
        $this->messages = array(
                                'invalid'  => 'Invalid input',
                                'notfound' => 'Not found',
                                'error'    => 'Internal error'
                                );
        // store exploded nts
        $this->exploded_nts = array();

        parent::__construct();
    }

    public function index()
    {
        echo 'Instructions page under construction';
    }

    public function getPdbInfo()
    {
        $pdb = $this->input->get_post('pdb');
        $cla = $this->input->get_post('cla');
        $res = $this->input->get_post('res');
        $this->load->model('Ajax_model', '', TRUE);
        $this->output->set_header("Access-Control-Allow-Origin: *");
        $this->output->set_header("Access-Control-Expose-Headers: Access-Control-Allow-Origin");
        echo $this->Ajax_model->get_pdb_info($pdb,$cla,$res);
    }

    public function getBulge()
    {
        // should be able to accept loop_id and unit_id

        // search POST, then GET
        $query = $this->input->get_post('quality');
        
        //$this->output->enable_profiler(TRUE);
        
        $query_type = $this->_parseInput($query);

        if ( $query_type ) {
            $this->output->set_header("Access-Control-Allow-Origin: *");
            $this->output->set_header("Access-Control-Expose-Headers: Access-Control-Allow-Origin");
            $data['json'] = $this->_database_lookup_bulge($query, $query_type);
            $this->load->view('json_view', $data);
        } else {
            echo $this->messages['invalid'];
        }

    }

    public function getCoordinates()
    {
        // should be able to accept loop_id, nt_ids, motif_id, short_nt_id
        // and loop pairs (returns the first loop of the two)

        // search POST, then GET
        $query = $this->input->get_post('coord');
        
        $this->output->enable_profiler(TRUE);
        
        $query_type = $this->_parseInput($query);

        if ( $query_type ) {
            $this->output->set_header("Access-Control-Allow-Origin: *");
            $this->output->set_header("Access-Control-Expose-Headers: Access-Control-Allow-Origin");
            $data['csv'] = $this->_database_lookup($query, $query_type);
            $this->load->view('csv_view', $data);
        } else {
            echo $this->messages['invalid'];
        }

    }

    public function getCoordinatesMotifAtlas()
    {
        // should be able to accept loop_id, nt_ids, motif_id, short_nt_id
        // and loop pairs (returns the first loop of the two)

        // search POST, then GET
        $query = $this->input->get_post('coord_ma');
        
        $this->output->enable_profiler(TRUE);
        
        $query_type = $this->_parseInput($query);

        if ( $query_type ) {
            $this->output->set_header("Access-Control-Allow-Origin: *");
            $this->output->set_header("Access-Control-Expose-Headers: Access-Control-Allow-Origin");
            $data['csv'] = $this->_database_lookup_ma($query, $query_type);
            $this->load->view('csv_view', $data);
        } else {
            echo $this->messages['invalid'];
        }

    }


    public function getCoordinatesRelative()
    {
        // should be able to accept loop_id, nt_ids, motif_id, short_nt_id
        // and loop pairs (returns the first loop of the two)

        // search POST, then GET
        $query = $this->input->get_post('core');
        
        $this->output->enable_profiler(TRUE);
        
        $query_type = $this->_parseInput($query);

        if ( $query_type ) {
            $this->output->set_header("Access-Control-Allow-Origin: *");
            $this->output->set_header("Access-Control-Expose-Headers: Access-Control-Allow-Origin");
            $data['csv'] = $this->_database_lookup_relative($query, $query_type);
            $this->load->view('csv_view', $data);
        } else {
            echo $this->messages['invalid'];
        }

    }


    public function getRSR()
    {
        // should be able to accept loop_id and unit_id

        // search POST, then GET
        $query = $this->input->get_post('quality');
        
        //$this->output->enable_profiler(TRUE);
        
        $query_type = $this->_parseInput($query);

        if ( $query_type ) {
            $this->output->set_header("Access-Control-Allow-Origin: *");
            $this->output->set_header("Access-Control-Expose-Headers: Access-Control-Allow-Origin");
            $data['json'] = $this->_database_lookup_RSR($query, $query_type);
            $this->load->view('json_view', $data);
        } else {
            echo $this->messages['invalid'];
        }

    }

    public function getRSRZ()
    {
        // should be able to accept loop_id and unit_id

        // search POST, then GET
        $query = $this->input->get_post('quality');
        
        // $this->output->enable_profiler(TRUE);
        
        $query_type = $this->_parseInput($query);

        if ( $query_type ) {
            $this->output->set_header("Access-Control-Allow-Origin: *");
            $this->output->set_header("Access-Control-Expose-Headers: Access-Control-Allow-Origin");
            $data['json'] = $this->_database_lookup_RSRZ($query, $query_type);
            $this->load->view('json_view', $data);
        } else {
            echo $this->messages['invalid'];
        }

    }

    private function _database_lookup($query, $query_type)
    {
        // don't load the database until the input was validated
        $this->load->model('Ajax_model', '', TRUE);

		$this->output->enable_profiler(TRUE);
		
        switch ($query_type) :
            case 'loop_id':
                return $this->Ajax_model->get_loop_coordinates($query);
            case 'motif_id':
                return $this->Ajax_model->get_exemplar_coordinates($query);
            case 'nt_list':
                //return $this->Ajax_model->get_coordinates($query);
                return $this->Ajax_model->get_unit_id_coordinates($query);
            case 'loop_pair':
                return $this->Ajax_model->get_loop_pair_coordinates($query);
            case 'unit_id':
                //return $this->Ajax_model->get_unit_id_coordinates($query);
                return $this->Ajax_model->get_nt_coordinates($query);
            default: return $this->messages['error'];
        endswitch;

    }

    private function _database_lookup_ma($query, $query_type)
    {
        // don't load the database until the input was validated
        $this->load->model('Ajax_model', '', TRUE);

        $this->output->enable_profiler(TRUE);
        
        switch ($query_type) :
            case 'loop_id':
                return $this->Ajax_model->get_loop_coordinates_MotifAtlas($query);
            default: return $this->messages['error'];
        endswitch;

    }

    private function _database_lookup_relative($query, $query_type)
    {
        // don't load the database until the input was validated
        $this->load->model('Ajax_model', '', TRUE);

        $this->output->enable_profiler(TRUE);
        
        switch ($query_type) :
            case 'unit_id':
                return $this->Ajax_model->get_coord_relative($query);
            default: return $this->messages['error'];
        endswitch;

    }

    private function _database_lookup_RSR($query, $query_type)
    {
        // don't load the database until the input was validated
        $this->load->model('Ajax_model', '', TRUE);

        //$this->output->enable_profiler(TRUE);
        
        switch ($query_type) :
            case 'loop_id':
                return $this->Ajax_model->get_loop_RSR($query);
            case 'motif_id':
                return $this->Ajax_model->get_exemplar_RSR($query);
            case 'unit_id':
                return $this->Ajax_model->get_unit_id_RSR($query);
            default: return $this->messages['error'];
        endswitch;

    }

    private function _database_lookup_RSRZ($query, $query_type)
    {
        // don't load the database until the input was validated
        $this->load->model('Ajax_model', '', TRUE);

        // $this->output->enable_profiler(TRUE);
        
        switch ($query_type) :
            case 'loop_id':
                return $this->Ajax_model->get_loop_RSRZ($query);
            case 'motif_id':
                return $this->Ajax_model->get_exemplar_RSRZ($query);
            case 'unit_id':
                return $this->Ajax_model->get_unit_id_RSRZ($query);
            default: return $this->messages['error'];
        endswitch;

    }

    private function _database_lookup_bulge($query, $query_type)
    {
        // don't load the database until the input was validated
        $this->load->model('Ajax_model', '', TRUE);

        // $this->output->enable_profiler(TRUE);
        
        switch ($query_type) :
            case 'loop_id':
                return $this->Ajax_model->get_bulge_RSRZ($query);
            case 'unit_id':
                return $this->Ajax_model->get_unit_RSRZ($query);
            default: return $this->messages['error'];
        endswitch;

    }

    private function _parseInput($query)
    {
        // if get_post returned FALSE, then
        if ( $query ) {

            if ( $this->_is_loop_id($query) ) {
                return 'loop_id';
            } elseif ( $this->_is_motif_id($query) ) {
                return 'motif_id';
            } elseif ( $this->_is_nt_list($query) ) {
                return 'nt_list';
            } elseif ( $this->_is_loop_pair($query) ) {
                return 'loop_pair';
            } elseif ( $this->_is_short_nt_list($query) ) {
                return 'short_nt_list';
            } elseif ( $this->_is_unit_id() ) {
                return 'unit_id';
            } else {
                return FALSE;
            }

        } else {
            return FALSE;
        }
    }

    private function _is_unit_id()
    {
        // 1S72|1|0|U|10, 3BNT|2|A|C|22||||4_665
        foreach ($this->exploded_nts as $nt) {
            $parts = explode('|', $nt);
            $separators = count($parts);
            if ( $separators >= 4 and $separators <= 9 and
                 $parts[1] != 'AU' and $parts[1] != 'BA1' ) {
                return TRUE;
            } else {
                return FALSE;
            }
        }
        return TRUE;
    }

    private function _is_loop_id($query)
    {
        // IL_1J5E_001
        if ( preg_match('/^(IL|HL|J3)_[0-9A-Z]{4}_\d{3}$/i', $query) ) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    private function _is_loop_pair($query)
    {
        // @IL_1J5E_001:IL_1S72_001 or IL_1J5E_001:@IL_1S72_001
        // "@" marks the loop for which the coordinates should be returned
        if ( preg_match('/^@?(IL|HL|J3)_[0-9A-Z]{4}_\d{3}:@?(IL|HL|J3)_[0-9A-Z]{4}_\d{3}$/i', $query) and
             substr_count($query, '@') == 1) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    private function _is_motif_id($query)
    {
        // IL_12345.89
        if ( preg_match('/^(IL|HL|J3)_\d{5}\.\d+$/i', $query) ) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    private function _is_nt_list($query)
    {
        // 1EKA_AU_1_A_1_G_,1EKA_AU_1_A_2_A_
        $this->exploded_nts = explode(',', $query);
        $pattern = '/^[A-Z0-9]{4}_[A-Z0-9]{2,3}_\d+_[A-Z0-9]{1}_-?\d{1,5}_[A-Z0-9]_[A-Z0-9]{0,1}$/i';

        foreach ($this->exploded_nts as $nt) {
            if ( ! preg_match($pattern, $nt) ) {
                return FALSE;
            }
        }
        return TRUE;
    }

    private function _is_short_nt_list($query)
    {
        // 1S72_1_0_1095
        // talk with Blake, implement later
        return FALSE;
    }

    function getMotifFlowJSON($motif_type, $release_id1, $release_id2)
    {
        $this->load->model('Motifs_model', '', TRUE);
        $this->output->set_header("Access-Control-Allow-Origin: *");
        $this->output->set_header("Access-Control-Expose-Headers: Access-Control-Allow-Origin");
        echo $this->Motifs_model->getSankeyDataJSON($release_id1, $release_id2, $motif_type);
    }

}
