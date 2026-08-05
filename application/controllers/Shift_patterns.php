<?php
class Shift_patterns extends CI_Controller
{
    function __construct()
    {
        parent::__construct();

        if (is_null(get_user())) {
            redirect("welcome");
        }

        if (!is_page_permitted('shift_patterns')) {
            redirect_if_not_permitted();
        }
    }

    function index() {
        $data['pageTitle'] = "Group Shifts";
        $data['active_menu'] = "shift_patterns";
        $this->load->view('header', $data);
        $data["menus"] = get_menus();
        $this->load->view('sidebar', $data);
        $this->load->view('shift_patterns/index', $data);
        $this->load->view('footer');
    }

    function create()
    {
        $data['pageTitle'] = "Create Group Shift";
        $data['active_menu'] = "shift_patterns";
        $this->load->view('header', $data);
        $data["menus"] = get_menus();
        $this->load->view('sidebar', $data);

        $this->load->view('shift_patterns/create', $data);
        $this->load->view('footer');
    }

    function edit($id) {
        $pattern = $this->db->where('id', $id)->get('shift_patterns')->row();

        $data['pageTitle'] = "Edit Group Shift - " . $pattern->name;
        $data['active_menu'] = "shift_patterns";
        $this->load->view('header', $data);
        $data["menus"] = get_menus();
        $this->load->view('sidebar', $data);

        $data["id"] = $id;
        $data["branch_id"] = $pattern->branch_id;

        $this->load->view('shift_patterns/create', $data);
        $this->load->view('footer');
    }

    function getShifts()
    {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $branch_id = $request->branch_id;

        $current_user = get_user();
        $cid = $current_user["company_id"];

        $shifts_query = $this->db->select("s.*, b.name as branch_name")->from("shifts s")
            ->join("branches b", "s.branch_id = b.id", "left")->where("s.company_id = '$cid'")->where('active', 1);

        if (!empty($branch_id)) {
            $shifts_query->where("(s.branch_id = '$branch_id' or s.is_leave = 'yes' or s.branch_id is null)");
        } else {
            $shifts_query->where("(s.is_leave = 'yes' or s.branch_id is null)");
        }

        $data['shifts'] = $shifts_query->order_by('s.is_leave DESC, s.name ASC')->get()->result();

        echo json_encode($data);
    }

    function getPattern()
    {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $id = $request->id;

        $days = [
            [
                'key' => 'mon',
                'name' => 'Mon'
            ],
            [
                'key' => 'tue',
                'name' => 'Tue'
            ],
            [
                'key' => 'wed',
                'name' => 'Wed'
            ],
            [
                'key' => 'thu',
                'name' => 'Thu'
            ],
            [
                'key' => 'fri',
                'name' => 'Fri'
            ],
            [
                'key' => 'sat',
                'name' => 'Sat'
            ],
            [
                'key' => 'sun',
                'name' => 'Sun'
            ]
        ];

        $pattern = array_map(function ($day) {
            return [
                'day' => $day['key'],
                'shift_id' => '',
                'code' => '',
                'color' => ''
            ];
        }, $days);

        if (empty($id)) {
            $patterns = [
                [
                    'week' => 1,
                    'pattern' => $pattern
                ]
            ];
            $name = '';
            $branch_id = '';
        } else {
            $existingPattern = $this->db->where('id', $id)->get('shift_patterns')->row();

            $patterns = $this->updateShifts(json_decode($existingPattern->pattern));
            $name = $existingPattern->name;
            $branch_id = $existingPattern->branch_id;
        }

        echo json_encode([
            'days' => $days,
            'patterns' => $patterns,
            'patternTemplate' => $pattern,
            'name' => $name,
            'branch_id' => $branch_id
        ]);
    }

    function savePattern()
    {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $branch_id = $request->branch_id;
        $name = $request->name;
        $pattern = $request->pattern;
        $id = $request->id;

        if (!empty($id)) {
            $this->db->set('weeks', count($pattern))->set('pattern', json_encode($pattern))->set('name', $name)->set('branch_id', $branch_id)->where('id', $id)->update('shift_patterns');
            echo json_encode([
                'status' => 'success',
                'message' => 'Group shift updated successfully'
            ]);
            return;
        }
        
        $current_user = get_user();
        $company_id = $current_user["company_id"];
        $created_by = $current_user["id"];

        $this->db->insert('shift_patterns', [
            'company_id' => $company_id,
            'branch_id' => $branch_id,
            'name' => $name,
            'weeks' => count($pattern),
            'pattern' => json_encode($pattern),
            'created_by' => $created_by
        ]);

        echo json_encode([
            'status' => 'success',
            'message' => 'Group shift saved successfully'
        ]);
    }

    function getPatterns()
    {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);

        $branch_id = $request->branch_id;
        $selected_branch_only = $request->selected_branch_only;

        if ($selected_branch_only && empty($branch_id)) {
            echo json_encode([
                'patterns' => []
            ]);
            return;
        }

        $current_user = get_user();
        $cid = $current_user["company_id"];
        $bid = $current_user["branch_id"];
        $permissions_level = $current_user["permissions_level"];

        $patterns_query = $this->db->select("sp.*, b.name as branch_name, e.first_name as created_by_name")->from("shift_patterns sp")
            ->join("branches b", "sp.branch_id = b.id", "left")
            ->join("employees e", "sp.created_by = e.id", "left")
            ->where("sp.company_id = '$cid'")
            ->where('sp.deleted_at is null');

        if ($permissions_level === "Outlet") {
            $patterns_query->where("(sp.branch_id = '$bid' or sp.branch_id = 0)");
        } else if (!empty($branch_id)) {
            $patterns_query->where("(sp.branch_id = '$branch_id' or sp.branch_id = 0)");   
        }

        $patterns = $patterns_query->order_by('sp.name ASC')->get()->result();

        foreach ($patterns as $key => $pattern) {
            $patterns[$key]->created_at = date("d M, Y h:i A", strtotime($pattern->created_at));
            $pattern = json_decode($pattern->pattern);
            $pattern = $this->updateShifts($pattern);
            $patterns[$key]->pattern = $pattern;
        }

        $data['patterns'] = $patterns;
        echo json_encode($data);
    }

    function deletePattern()
    {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $id = $request->id;

        $deleted_at = date("Y-m-d H:i:s");
        $this->db->set('deleted_at', $deleted_at)->where('id', $id)->update('shift_patterns');
        echo json_encode([
            'status' => 'success',
            'message' => 'Group shift deleted successfully'
        ]);
    }

    function updateShifts($pattern)
    {
        $shift_ids = [];

        foreach ($pattern as $week) {
            foreach ($week->pattern as $day) {
                if ($day->shift_id) {
                    $shift_ids[] = $day->shift_id;
                }
            }
        }

        // remove duplicates
        $shift_ids = array_unique($shift_ids);

        if (empty($shift_ids)) {
            return $pattern;
        }

        $shifts = $this->db->select('id, code, color')->where_in('id', $shift_ids)->get('shifts')->result();

        foreach ($shifts as $shift) {
            foreach ($pattern as $week) {
                foreach ($week->pattern as $day) {
                    if ($day->shift_id == $shift->id) {
                        $day->code = $shift->code;
                        $day->color = $shift->color;
                    }
                }
            }
        }

        return $pattern;
    }
}
