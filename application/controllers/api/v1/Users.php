<?php defined('BASEPATH') or exit('No direct script access allowed');

class Users extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('api_model');
        $this->load->helper(array('common_helper'));
        header('Content-Type: application/json');
    }

    public function createUser()
    {
        $inputData = json_decode(trim(file_get_contents('php://input')), true);

        $data['userId'] = uniqid();
        $data['token'] = generateToken(32);

        $data['name'] = $inputData['name'];
        $data['username'] = $inputData['username'];
        $data['email'] = $inputData['email'];
        $data['password'] = hash('sha256', $inputData['password']);

        $response =  $this->api_model->createUser($data);

        header('Content-Type: application/json');
        echo json_encode($response);
    }


    public function userLogin()
    {
        $inputData = json_decode(trim(file_get_contents('php://input')), true);

        $data['email'] = $inputData['email'];
        $data['password'] = hash('sha256', $inputData['password']);

        $loginResponse =  $this->api_model->userLogin($data);

        if ($loginResponse['status'] == true) {
            $statsResponse = $this->api_model->getUserStats($loginResponse['userData']['userId']);

            if ($statsResponse['status'] == true) {
                $response = array('status' => true, 'message' => 'User Logged in Successfully', 'userData' => $loginResponse['userData'],  'userStats' => $statsResponse['userStats']);
            }
        } else {
            if ($loginResponse['message'] == 'Incorrect Login') {
                $response = array('status' => false, 'message' => 'Incorrect Login Details');
            } else {
                $response = array('status' => false, 'message' => 'Something went wrong on user login');
            }
        }

        header('Content-Type: application/json');
        echo json_encode($response);
    }

    public function getUsers()
    {
        $response = $this->api_model->getUsers();

        header('Content-Type: application/json');
        echo json_encode($response);
    }

    public function getUserById($userId)
    {
        $response = $this->api_model->getUserById($userId);

        header('Content-Type: application/json');
        echo json_encode($response);
    }

    public function createGroup()
    {
        $inputData = json_decode(trim(file_get_contents('php://input')), true);

        $groupData['groupId'] = uniqid();
        $groupData['name'] = $inputData['name'];
        $groupData['description'] = $inputData['description'];
        $groupData['createdBy'] = $inputData['createdBy'];

        $memberData['memberId'] = uniqid();
        $memberData['userId'] = $inputData['createdBy'];
        $memberData['groupId'] = $groupData['groupId'];

        $groupResponse = $this->api_model->createGroup($groupData);
        $memberResponse = $this->api_model->createGroupMember($memberData);

        if ($groupResponse['status'] == true && $memberResponse['status'] == true) {
            $response = array('status' => true, 'message' => 'Group Created Successfully And Member Added', 'group' => $groupResponse['group'],  'groupMember' => $memberResponse['groupMember']);
        } else if ($groupResponse['status'] == true && $memberResponse['status'] == false) {
            $response = array('status' => false, 'message' => 'Something went wrong on member creation');
        } else {
            $response = array('status' => false, 'message' => 'Something went wrong on group creation');
        }

        header('Content-Type: application/json');
        echo json_encode($response);
    }

    public function getGroups()
    {
        $response = $this->api_model->getGroups();

        header('Content-Type: application/json');
        echo json_encode($response);
    }

    public function getGroupById($groupId)
    {
        $response = $this->api_model->getGroupById($groupId);

        header('Content-Type: application/json');
        echo json_encode($response);
    }

    public function createGroupMember()
    {
        $inputData = json_decode(trim(file_get_contents('php://input')), true);

        $data['memberId'] = uniqid();

        $data['userId'] = $inputData['userId'];
        $data['groupId'] = $inputData['groupId'];

        $response = $this->api_model->createGroupMember($data);

        header('Content-Type: application/json');
        echo json_encode($response);
    }

    public function getMembersByGroup($groupId)
    {
        $response = $this->api_model->getMembersByGroup($groupId);

        header('Content-Type: application/json');
        echo json_encode($response);
    }


    public function createExpense()
    {
        $inputData = json_decode(trim(file_get_contents('php://input')), true);

        $newExpense['expenseId'] = uniqid();

        $newExpense['groupId'] = $inputData['groupId'];
        $newExpense['description'] = $inputData['description'];
        $newExpense['amount'] = $inputData['amount'];
        $newExpense['payer'] = $inputData['payer'];
        $amountType = $inputData['amountType']; //percent , number
        $splitUsers = $inputData['splitUsers']; //selected list of users for the specific expense
        // [{"memberId":"63abf5a590d16","amount":"40"},{"memberId":"63abf8c739760","amount":"60"}]
        $splitValue = array();
        $shareByUpdateErrorFlag = false;

        $payedBy['column'] = "total_paid";
        $payedBy['amount'] = $newExpense['amount'];
        $payedBy['memberId'] = $newExpense['payer'];

        $payedByResponse = $this->api_model->updateGroupMembers($payedBy);
        $shareByResponses = array();
        $shareByResponse = "";
        if ($amountType == "percent") {
            foreach ($splitUsers as $userKey => $splitUser) {
                $splitItem = array();
                $actualAmount = ($newExpense['amount'] * $splitUser['amount']) / 100;
                $splitItem['memberId'] = $splitUser['memberId'];
                $splitItem['amount'] = $actualAmount;
                array_push($splitValue, $splitItem);
                $splitItem['column'] = "total_share";
                $shareByResponse = $this->api_model->updateGroupMembers($splitItem);
                array_push($shareByResponses, $shareByResponse);
                if ($shareByResponse['status'] == false) {
                    $shareByUpdateErrorFlag = true;
                }
            }
            $newExpense['splitValue'] = json_encode($splitValue);
        } else {
            foreach ($splitUsers as $userKey => $splitUser) {
                $splitItem = array();
                $splitItem['memberId'] = $splitUser['memberId'];
                $splitItem['amount'] = $splitUser['amount'];
                $splitItem['column'] = "total_share";
                $shareByResponse = $this->api_model->updateGroupMembers($splitItem);
                array_push($shareByResponses, $shareByResponse);
                if ($shareByResponse['status'] == false) {
                    $shareByUpdateErrorFlag = true;
                }
            }
            $newExpense['splitValue'] = json_encode($splitUsers);
        }

        $expenseResponse = $this->api_model->createExpense($newExpense);

        if ($shareByUpdateErrorFlag == false && $payedByResponse['status'] == true && $expenseResponse['status'] == true) {
            $response = array('status' => true, 'message' => 'Expenses Added Successfully', 'Expenses' => $newExpense);
        } else {
            $response = array('status' => false, 'message' => 'Expenses Adding Failed', 'payedByResponse' => $payedByResponse, 'shareByUpdateErrorFlag' => $shareByUpdateErrorFlag, 'expenseResponse' => $expenseResponse, 'shareByResponses' => $shareByResponses);
        }

        header('Content-Type: application/json');
        echo json_encode($response);
    }

    public function getExpensesByGroup($groupId)
    {
        $response = $this->api_model->getExpensesByGroup($groupId);

        header('Content-Type: application/json');
        echo json_encode($response);
    }

    public function getExpenseDetails($expenseId)
    {
        $expenseResponse = $this->api_model->getExpenseDetails($expenseId);
        $expenseDetails = array();

        if ($expenseResponse['status'] == true) {
            $expenseDetails['paidBy'] = $this->api_model->getNameByMemberId($expenseResponse['expense']['payer']);
            $expenseDetails['description'] = $expenseResponse['expense']['description'];
            $expenseDetails['amount'] = $expenseResponse['expense']['amount'];
            $split = json_decode($expenseResponse['expense']['split'], true);
            $splitData = array();
            foreach ($split as $splitKey => $splitValue) {
                $splitData[$splitKey]['name'] = $this->api_model->getNameByMemberId($splitValue['memberId']);
                $splitData[$splitKey]['amount'] = $splitValue['amount'];
            }
            $expenseDetails['split'] = $splitData;
            $response = array('status' => true, 'message' => 'Expense Fetched Successfully', 'Expense' => $expenseDetails);
        } else {
            $response = array('status' => false, 'message' => 'Expense Fetched Failed');
        }
        header('Content-Type: application/json');
        echo json_encode($response);
    }

    public function getBalances($groupId)
    {
        $response = $this->api_model->getBalances($groupId);

        header('Content-Type: application/json');
        echo json_encode($response);
    }

    public function getGroupsByUserId($userId)
    {
        $response = $this->api_model->getGroupsByUserId($userId);

        header('Content-Type: application/json');
        echo json_encode($response);
    }

    public function getUserStats($userId)
    {
        $response = $this->api_model->getUserStats($userId);

        header('Content-Type: application/json');
        echo json_encode($response);
    }
}
