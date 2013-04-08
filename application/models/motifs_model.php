<?php

function add_url($n)
{
    return anchor(base_url(array('motif','view',$n)), $n);
}

class Motifs_model extends CI_Model {

    function __construct()
    {
        $CI = & get_instance();
        $CI->load->helper('url');
        $CI->load->helper('form');

        $this->types = array('IL','HL');
        // Call the Model constructor
        parent::__construct();
    }

    function get_current_release_info()
    {
        $ils = $this->get_latest_release('il', 1);

        $data['release_info']['il_release'] = $ils['id'];
        $data['release_info']['hl_release'] = $this->get_latest_release('hl');
        $data['release_info']['last_update'] = strtotime($ils['date']);
        $data['release_info']['next_update'] = strtotime("{$ils['date']} + 4 weeks");

        return $data;
    }

    function get_featured_motifs($motif_type)
    {
        $release_id = $this->get_latest_release($motif_type);

        if ( $motif_type == 'il' ) {
            $motifs = array('kink-turn', 'c-loop', 'sarcin', 'triple sheared', 'double sheared');
        } else {
            $motifs = array('T-loop', 'GNRA');
        }
        $data = array();

        foreach($motifs as $motif) {
            $this->db->select('*, count(ml_loops.id) as members')
                     ->from('ml_motif_annotations')
                     ->join('ml_loops', 'ml_motif_annotations.motif_id = ml_loops.motif_id')
                     ->like('ml_motif_annotations.common_name', $motif)
                     ->where('release_id', $release_id)
                     ->group_by('ml_loops.motif_id')
                     ->order_by('members', 'desc')
                     ->limit(1);
            $query = $this->db->get();
            if ( $query->num_rows() > 0 ) {
                $data[$motif] = $query->row()->motif_id;
            }
        }
        return $data;
    }

    function get_all_motifs($release_id, $motif_type)
    {
        if ( $release_id == 'current' ) {
            $release_id = $this->get_latest_release($motif_type);
        }

        $this->db->select('id')
                 ->from('ml_motifs')
                 ->where('release_id', $release_id)
                 ->where('type', $motif_type);
        $query = $this->db->get();
        $motif_ids = array();
        foreach($query->result() as $row) {
            $motif_ids[] = $row->id;
        }
        return $motif_ids;
    }

    function db_get_all_releases($motif_type)
    {
        $this->db->select('STRAIGHT_JOIN ml_releases.*,count(ml_loops.id) AS loops, count(DISTINCT(motif_id)) AS motifs', FALSE)
                 ->from('ml_releases')
                 ->join('ml_loops','ml_releases.id=ml_loops.release_id')
                 ->where('ml_releases.type',$motif_type)
                 ->like('ml_loops.id',$motif_type,'after')
                 ->group_by('ml_releases.id')
                 ->order_by('ml_releases.date','desc');
        return $this->db->get();
    }

    // get motifs with same sequences
    function get_polymorphs($motif_type, $release_id)
    {
        $query_string = "
            seq, length, group_concat(motif_id) AS motifs, count(motif_id) AS motif_num
            FROM (
                SELECT DISTINCT(seq AND motif_id),seq, length, motif_id FROM ml_loops AS t1
                JOIN loops_all AS t2
                ON t1.id = t2.id
                WHERE t1.release_id = '{$release_id}'
                AND t2.`type` = '{$motif_type}'
                ORDER BY length DESC
            ) AS t3
            GROUP BY seq
            HAVING count(motif_id) > 1
            ORDER BY length DESC;
        ";
        $query = $this->db->select($query_string, FALSE)->get();

        if ($query->num_rows() == 0) { return 'No polymorphs found in this release'; }

        $table = array();
        foreach ($query->result() as $row) {
            $table[] = array($row->seq,
                             $row->length,
                             $row->motif_num,
                             $this->format_polymorphic_motif_list($row->motifs) );
        }
        return $table;
    }

    function format_polymorphic_motif_list($motif_list)
    {
        $motifs = explode(',', $motif_list);
        $output = '<ul class="inputs-list">';
        foreach ($motifs as $motif) {
            $loop_link = anchor_popup("motif/view/$motif", '&#10140;');
            $shuffled = str_shuffle($motif); // to avoid id collision
            $output .=
           "<li class='loop'>
                <label>
                    <input type='radio' class='jmolInline' name='m' data-coord='{$motif}' id='{$shuffled}'>
                    <span>$motif</span>
                    <span class='loop_link'>{$loop_link}</span>
                </label>
            </li>";
        }
        $compare_link = anchor_popup(base_url(array('motif', 'compare', $motifs[0], $motifs[1])), 'Compare');
        return $output . "<li>$compare_link</li></ul>";
    }

    function get_change_counts_by_release($motif_type)
    {
        $this->db->select('release_id1')
                 ->select_sum('num_added_groups','nag')
                 ->select_sum('num_removed_groups','nrg')
                 ->select_sum('num_updated_groups','nug')
                 ->from('ml_release_diff')
                 ->where('type',$motif_type)
                 ->where('direct_parent',1)
                 ->group_by('release_id1');
        $query = $this->db->get();
        foreach ($query->result() as $row) {
            $changes[$row->release_id1] = $row->nag + $row->nug + $row->nrg;
        }
        return $changes;
    }

    function get_release_precedence($motif_type)
    {
        $this->db->select('id')
                 ->from('ml_releases')
                 ->where('type',$motif_type)
                 ->order_by('date','desc');
        $query = $this->db->get();
        foreach ($query->result() as $row) {
            $ids[] = $row->id;
        }
        for ($i=0; $i<count($ids)-1; $i++) {
            $releases[$ids[$i]] = $ids[$i+1];
        }
        return $releases;
    }

    function get_release_status($motif_type,$id)
    {
        $this->db->select()
                 ->from('ml_releases')
                 ->where('type',$motif_type)
                 ->order_by('date','desc')
                 ->limit(1);
        $query = $this->db->get();
        foreach ($query->result() as $row) {
            $current_release = $row->id;
        }
        if ($id == $current_release) {
            return 'Current';
        } else {
            return 'Obsolete';
        }
    }

    function get_label_type($changes)
    {
        if ($changes == 0) {
            $label = 'success';
        } elseif ($changes <= 20) {
            $label = 'notice';
        } elseif ($changes <= 100) {
            $label = 'warning';
        } else {
            $label = 'important';
        }
        return $label;
    }

    function make_release_label($num, $id1, $id2, $motif_type)
    {
        $text = anchor(base_url(array('motifs','compare',$motif_type,$id1,$id2)), $num);
        if ($num == 0) {
            return "<span class='label default'>$text</span>";
        } elseif ($num <= 10) {
            return "<span class='label notice'>$text</span>";
        } elseif ($num <= 100) {
            return "<span class='label warning'>$text</span>";
        } else {
            return "<span class='label important'>$text</span>";
        }
    }

    function get_all_releases()
    {
        foreach ($this->types as $motif_type) {
            $changes = $this->get_change_counts_by_release($motif_type);
            $compare = $this->get_release_precedence($motif_type);
            $query   = $this->db_get_all_releases($motif_type);

            $i = 0;
            foreach ($query->result() as $row) {
                if ($i == 0) {
                    $id = anchor(base_url(array("motifs/release",$motif_type,$row->id)), $row->id . ' (current)');
                    $i++;
                } else {
                    $id = anchor(base_url(array("motifs/release",$motif_type,$row->id)), $row->id);
                }
                if (array_key_exists($row->id, $changes)) {
                    $label = $this->get_label_type($changes[$row->id]);
                    $compare_url = base_url(array('motifs','compare',$motif_type,$row->id,$compare[$row->id]));
                    $num_changes = "<a href='$compare_url' class='nodec'><span class='label {$label}'>{$changes[$row->id]} changes</span></a>";
                } else {
                    $num_changes = '';
                }

                $table[$motif_type][] = array($id,
                                        $num_changes,
                                        $row->description,
                                        $row->loops,
                                        $row->motifs);
            }
        }
        return $table;
    }

    function get_complete_release_history()
    {
        foreach ($this->types as $motif_type) {

            $query = $this->db_get_all_releases($motif_type);
            foreach($query->result() as $row){
                $data[$row->id]['loops'] = $row->loops;
                $data[$row->id]['motifs'] = $row->motifs;
                $data[$row->id]['description'] = $row->description;
                $data[$row->id]['annotation'] = $row->annotation;
                $data[$row->id]['date'] = $row->date;
            }

            $releases = $this->get_release_precedence($motif_type);

            $this->db->select()
                     ->from('ml_releases')
                     ->join('ml_release_diff','ml_releases.id=ml_release_diff.release_id1')
                     ->where('ml_releases.type',$motif_type)
                     ->where('ml_release_diff.type',$motif_type)
                     ->where('direct_parent',1)
                     ->order_by('date','desc');
            $query = $this->db->get();

            foreach ($query->result() as $row) {
                if ($row->release_id2 == $releases[$row->id]) {
                    $table[$motif_type][] = array(
                        anchor(base_url(array('motifs','release',$motif_type,$row->id)), $row->id),
                        $this->make_release_label($row->num_added_groups, $row->id, $releases[$row->id], $motif_type),
                        $this->make_release_label($row->num_removed_groups, $row->id, $releases[$row->id], $motif_type),
                        $this->make_release_label($row->num_updated_groups, $row->id, $releases[$row->id], $motif_type),
                        $this->make_release_label($row->num_added_loops, $row->id, $releases[$row->id], $motif_type),
                        $this->make_release_label($row->num_removed_loops, $row->id, $releases[$row->id], $motif_type),
                        $data[$row->id]['loops'],
                        $data[$row->id]['motifs'],
                        date('m-d-Y', strtotime($data[$row->id]['date'])),
                        $data[$row->id]['annotation']
                    );
                }
            }

            // show the first release that has nothing to compare it with
            $table[$motif_type][] = array(
                anchor(base_url(array('motifs','release',$motif_type,'0.1')), '0.1'),
                0,
                0,
                0,
                0,
                0,
                $data['0.1']['loops'],
                $data['0.1']['motifs'],
                date('m-d-Y', strtotime($data['0.1']['date'])),
                $data['0.1']['annotation']
            );



        }
        return $table;
    }

    function get_annotation_label_type($comment)
    {
        if ($comment == 'Exact match') {
            return 'success';
        } elseif ($comment == 'New id, no parents') {
            return 'notice';
        } elseif ($comment == '> 2 parents') {
            return 'important';
        } else {
            return 'warning';
        }
    }

    function add_annotation_label($class_id,$reason)
    {
        if (array_key_exists($class_id,$reason)) {
            $label = $this->get_annotation_label_type($reason[$class_id]);
            return " <span class='label $label'>{$reason[$class_id]}</span>";
        } else {
            return '';
        }
    }

    function get_graphml($motif_type, $id)
    {
        $this->db->select('graphml')
                 ->from('ml_releases')
                 ->where('type',$motif_type)
                 ->where('id',$id);
        $query = $this->db->get();
        foreach ($query->result() as $row) {
            return $graphml = $row->graphml;
        }
    }

    function make_fancybox_link($id, $motif_type, $release_id)
    {
         $image = 'http://rna.bgsu.edu/img/MotifAtlas/' . strtoupper($motif_type) . $release_id . '/' . $id . '.png';
         return "<ul class='media-grid'><li><a href='#$id'><img class='thumbnail' src='$image' alt='$id' class='varna' /></a></li></ul>";
    }

    function get_release($motif_type,$id)
    {
        // get annotations: updated/>2 parents etc
        $this->db->select()
                 ->from('ml_motifs')
                 ->where('type',$motif_type)
                 ->where('release_id',$id);
        $query = $this->db->get();
        foreach ($query->result() as $row) {
            $reason[$row->id]  = $row->comment;
            $reason_flat[]     = $row->comment;
        }
        // count all annotation types
        $counts = array_count_values($reason_flat);
        $counts_text = '';
        foreach ($counts as $comment => $count) {
            $label = $this->get_annotation_label_type($comment);
            $counts_text .= "<span class='label $label'>$comment</span> <strong>$count</strong>;    ";
        }
        $counts_text .= '<br><br>';

        // get common names and annotations
        $this->db->select()
                 ->from('ml_motif_annotations');
        $query = $this->db->get();
        foreach ($query->result() as $row) {
            $annotations[$row->motif_id]['bp_signature'] = $row->bp_signature;
            $annotations[$row->motif_id]['common_name'] = $row->common_name;
        }

        // get the motif ids and counts
        $this->db->select('motif_id,count(id) AS instances')
                 ->from('ml_loops')
                 ->like('motif_id',strtoupper($motif_type),'after')
                 ->where('release_id', $id)
                 ->group_by('motif_id')
                 ->order_by('instances','desc');
        $query = $this->db->get();

        $i = 1;
        foreach ($query->result() as $row) {
            if ( array_key_exists($row->motif_id, $annotations) &&
                 strlen($annotations[$row->motif_id]['common_name']) > 1 ) {
                $annotation = '<li>Name: ' . $annotations[$row->motif_id]['common_name'] . '</li>';
            } else {
                $annotation = '';
            }

            if ( array_key_exists($row->motif_id, $annotations) and array_key_exists('bp_signature', $annotations[$row->motif_id])) {
                $signature = $annotations[$row->motif_id]['bp_signature'];
            } else {
                $signature = '';
            }

            $length_distribution = $this->_get_motif_length_distribution($row->motif_id, $id);

            $table[] = array($i,
                             $this->make_fancybox_link($row->motif_id, $motif_type, $id),
                             anchor_popup(base_url(array('motif','view',$row->motif_id)), $row->motif_id)
                                . "<ul class='unstyled inputs-list'>"
                                . "<li><label><input type='radio' class='jmolInline' id='"
                                . str_replace('.','_',$row->motif_id)
                                . "' data-coord='{$row->motif_id}' data-type='motif_id' name='ex'>"
                                . "<span>Exemplar</span></label></li>"
                                . "<li>Basepair signature: $signature</li>"
                                . '<li>History status: ' . $this->add_annotation_label($row->motif_id, $reason) . '</li>'
                                . "$annotation"
                                . '</ul>',
                             $length_distribution['min'],
                             $row->instances);
            $i++;
        }
        return array( 'table' => $table, 'counts' => $counts_text );
    }

    function get_release_advanced($motif_type, $release_id)
    {
        $result = $this->get_release($motif_type, $release_id);

        $table = array();
        foreach ($result['table'] as $row) {

            preg_match('/([IH]L_\d{5}\.\d+)/', $row[2], $matches);

            $motif_id = $matches[0];

            $distribution = $this->_get_motif_length_distribution($motif_id, $release_id);

            $row[] = $distribution['min'];
            $row[] = $distribution['max'];
            $row[] = $distribution['diff'];

            $table[] = $row;
        }

        $result['table'] = $table;
        return $result;
    }

    function _get_motif_length_distribution($motif_id, $release_id)
    {
        $this->db->select('loops_all.length')
                 ->from('ml_loops')
                 ->join('loops_all', 'ml_loops.id=loops_all.id')
                 ->where('ml_loops.release_id', $release_id)
                 ->where('ml_loops.motif_id', $motif_id);
        $query = $this->db->get();

        foreach($query->result() as $row) {
            $length[] = $row->length;
        }

        $distribution['max'] = max($length);
        $distribution['min'] = min($length);
        $distribution['diff'] = $distribution['max'] - $distribution['min'];

        return $distribution;
    }

    function get_compare_radio_table()
    {
        foreach ($this->types as $motif_type) {
            $changes = $this->get_change_counts_by_release($motif_type);
            $query   = $this->db_get_all_releases($motif_type);
            foreach ($query->result() as $row) {
                if (array_key_exists($row->id, $changes)) {
                    $label = $this->get_label_type($changes[$row->id]);
                    $num_changes = "<span class='label {$label}'>{$changes[$row->id]} changes</span>";
                } else {
                    $num_changes = '';
                }

                $table[$motif_type][] = form_radio(array('name'=>'release1','value'=>$row->id)) . $row->id . $num_changes;
                $table[$motif_type][] = form_radio(array('name'=>'release2','value'=>$row->id)) . $row->id;
            }
        }
        return $table;
    }

    function get_latest_release($motif_type, $date=NULL)
    {
        $this->db->select()
                 ->from('ml_releases')
                 ->where('type',$motif_type)
                 ->order_by('date','desc')
                 ->limit(1);
        $result = $this->db->get()->row();
        if ( $date ) {
            return array('id' => $result->id, 'date' => $result->date);
        } else {
            return $result->id;
        }
    }

    function count_motifs($motif_type,$rel)
    {
        $this->db->select('count(id) as ids')
                 ->from('ml_motifs')
                 ->where('release_id', $rel)
                 ->where('type',$motif_type);
        $query = $this->db->get();

        foreach ($query->result() as $row) {
            $counts = $row->ids;
        }
        return $counts;
    }

    function get_release_diff($motif_type,$rel1, $rel2)
    {
        $attributes = array('class' => 'unstyled');

        $this->db->select()
                 ->from('ml_release_diff')
                 ->where('type',$motif_type)
                 ->where('release_id1',$rel1)
                 ->where('release_id2',$rel2);
        $query = $this->db->get();
        if ($query->num_rows == 0) {
            $this->db->select()
                     ->from('ml_release_diff')
                     ->where('type',$motif_type)
                     ->where('release_id1',$rel2)
                     ->where('release_id2',$rel1);
            $query = $this->db->get();
            $data['rel1'] = $rel2;
            $data['rel2'] = $rel1;
            $rel1 = $data['rel1'];
            $rel2 = $data['rel2'];
        } else {
            $data['rel1'] = $rel1;
            $data['rel2'] = $rel2;
        }

        $counts1 = $this->count_motifs($motif_type,$rel1);
        $counts2 = $this->count_motifs($motif_type,$rel2);

        foreach ($query->result() as $row) {

            $data['uls']['num_motifs1'] = $counts1;
            $data['uls']['num_motifs2'] = $counts2;

            if ($row->num_same_groups > 0) {
                $data['uls']['ul_intersection'] = ul(array_map("add_url", split(', ',$row->same_groups)),$attributes);
            } else {
                $data['uls']['ul_intersection'] = '';
            }
            if ($row->num_updated_groups > 0) {
                $data['uls']['ul_updated'] = ul(array_map("add_url", split(', ',$row->updated_groups)),$attributes);
            } else {
                $data['uls']['ul_updated'] = '';
            }
            if ($row->num_added_groups > 0) {
                $data['uls']['ul_only_in_1'] = ul(array_map("add_url", split(', ',$row->added_groups)),$attributes);
            } else {
                $data['uls']['ul_only_in_1'] = '';
            }
            if ($row->num_removed_groups > 0) {
                $data['uls']['ul_only_in_2'] = ul(array_map("add_url", split(', ',$row->removed_groups)),$attributes);
            } else {
                $data['uls']['ul_only_in_2'] = '';
            }
            $data['uls']['num_intersection'] = $row->num_same_groups;
            $data['uls']['num_updated']      = $row->num_updated_groups;
            $data['uls']['num_only_in_1']    = $row->num_added_groups;
            $data['uls']['num_only_in_2']    = $row->num_removed_groups;
        }
        return $data;
    }


}

/* End of file motifs_model.php */
/* Location: ./application/model/motifs_model.php */