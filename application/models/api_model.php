<?php
class Api_model extends CI_Model
{
    function createUser($data)
    {
        try {
            $emailUsedCount = $this->db->select('email')
                ->from('users')
                ->where('email', $data['email'])
                ->count_all_results();

            if ($emailUsedCount > 0) {
                $response = array('status' => false, 'message' => 'Email ID already used');
            } else {
                $newUser = array(
                    'user_id' => $data['userId'],
                    'name' => $data['name'],
                    'username' => $data['username'],
                    'email' => $data['email'],
                    'password' => $data['password'],
                    'token' =>    $data['token'],
                    'created_at' => date("Y-m-d H:i:s"),
                    'is_active' => '1'
                );

                $this->db->insert('users', $newUser);

                $response = array('status' => true, 'message' => 'User Created Successfully', 'user' => $newUser);
            }
        } catch (Exception $e) {
            $response = array('status' => false, 'message' => 'Something Went Wrong');
        }
        return $response;
    }

    function userLogin($data)
    {
        try {
            $usersArray = $this->db->select('user_id as userId, name as fullName, username')
                ->from('users')
                ->where('email', $data['email'])
                ->where('password', $data['password'])
                ->get()->result_array();

            $userCount = count($usersArray, 0);
            if ($userCount == 1) {
                $response = array('status' => true, 'message' => 'User Logged in Successfully', 'userData' => $usersArray[0]);
            } else {
                $response = array('status' => false, 'message' => 'Incorrect Login');
            }
        } catch (Exception $e) {
            $response = array('status' => false, 'message' => 'Something Went Wrong');
        }
        return $response;
    }

    function getUsers()
    {
        try {
            $usersArray = $this->db->select('user_id as userId, name as fullName, username, email')
                ->from('users')
                ->get()->result_array();

            $response = array('status' => true, 'message' => 'Users Fetched Successfully', 'users' => $usersArray);
        } catch (Exception $e) {
            $response = array('status' => false, 'message' => 'Something Went Wrong');
        }
        return $response;
    }

    function getUserById($userId)
    {
        try {
            $usersArray = $this->db->select('user_id as userId, name as fullName, username, email')
                ->from('users')
                ->where('user_id', $userId)
                ->get()->result_array();

            $response = array('status' => true, 'message' => 'User Fetched Successfully', 'user' => $usersArray[0]);
        } catch (Exception $e) {
            $response = array('status' => false, 'message' => 'Something Went Wrong');
        }
        return $response;
    }

    function createGroup($data)
    {
        try {
            $newGroup = array(
                'group_id' => $data['groupId'],
                'name' => $data['name'],
                'description' => $data['description'],
                'created_by' => $data['createdBy'],
                'created_at' => date("Y-m-d H:i:s"),
            );

            $this->db->insert('groups', $newGroup);

            $response = array('status' => true, 'message' => 'Group Created Successfully', 'group' => $newGroup);
        } catch (Exception $e) {
            $response = array('status' => false, 'message' => 'Something Went Wrong');
        }
        return $response;
    }


    function createGroupMember($data)
    {
        try {
            $memberCount = $this->db->select('member_id')
                ->from('group_members')
                ->where('fk_user_id', $data['userId'])
                ->where('fk_group_id', $data['groupId'])
                ->count_all_results();

            if ($memberCount > 0) {
                $response = array('status' => false, 'message' => 'Member already present');
            } else {

                $newMember = array(
                    'member_id' => $data['memberId'],
                    'fk_user_id' => $data['userId'],
                    'fk_group_id' => $data['groupId'],
                );

                $this->db->insert('group_members', $newMember);

                $response = array('status' => true, 'message' => 'Member Added Successfully', 'groupMember' => $newMember);
            }
        } catch (Exception $e) {
            $response = array('status' => false, 'message' => 'Something Went Wrong');
        }
        return $response;
    }

    function getGroups()
    {
        try {
            $groupsArray = $this->db->select('groups.group_id as groupId, groups.name, groups.description, users.name as createdBy')
                ->from('groups')
                ->join('users', 'groups.created_by = users.user_id')
                ->get()->result_array();

            $response = array('status' => true, 'message' => 'Groups Fetched Successfully', 'groups' => $groupsArray);
        } catch (Exception $e) {
            $response = array('status' => false, 'message' => 'Something Went Wrong');
        }
        return $response;
    }

    function getGroupById($groupId)
    {
        try {
            $groupsArray = $this->db->select('groups.group_id as groupId, groups.name, groups.description, users.name as createdBy, SUM(expenses.amount) as totalSpend')
                ->from('groups')
                ->join('users', 'groups.created_by = users.user_id')
                ->join('expenses', 'groups.group_id = expenses.fk_group_id')
                ->where('group_id', $groupId)
                ->get()->result_array();

            $response = array('status' => true, 'message' => 'Group Fetched Successfully', 'group' => $groupsArray[0]);
        } catch (Exception $e) {
            $response = array('status' => false, 'message' => 'Something Went Wrong');
        }
        return $response;
    }

    function getMembersByGroup($groupId)
    {
        try {
            $groupsArray = $this->db->select('group_members.member_id as memberId, users.name as fullName, group_members.total_paid as totalPaid, group_members.total_share as totalShare')
                ->from('group_members')
                ->join('users', 'group_members.fk_user_id = users.user_id')
                ->where('fk_group_id', $groupId)
                ->get()->result_array();

            $response = array('status' => true, 'message' => 'Group Members Fetched Successfully', 'groupMembers' => $groupsArray);
        } catch (Exception $e) {
            $response = array('status' => false, 'message' => 'Something Went Wrong');
        }
        return $response;
    }

    function updateGroupMembers($data)
    {
        try {
            $dataColumn = $data['column'];
            $dataValue = $data['amount'];
            $memberId = $data['memberId'];
            $this->db->set($dataColumn, $dataColumn . ' + ' .  $dataValue, FALSE)
                ->where('member_id', $memberId)
                ->update('group_members');

            $response = array('status' => true, 'message' => 'Group Member Updated Successfully');
        } catch (Exception $e) {
            $response = array('status' => false, 'message' => 'Something Went Wrong');
        }
        return $response;
    }


    function createExpense($data)
    {
        try {
            $newExpense = array(
                'expense_id' => $data['expenseId'],
                'fk_group_id' => $data['groupId'],
                'description' => $data['description'],
                'amount' => $data['amount'],
                'payer' => $data['payer'],
                'split' => $data['splitValue'],
            );

            $this->db->insert('expenses', $newExpense);

            $response = array('status' => true, 'message' => 'Expenses Added Successfully', 'expenses' => $newExpense);
        } catch (Exception $e) {
            $response = array('status' => false, 'message' => 'Something Went Wrong');
        }
        return $response;
    }

    function getExpensesByGroup($groupId)
    {
        try {
            $expensesArray = $this->db->select('expense_id as expenseId, description, amount')
                ->from('expenses')
                ->where('fk_group_id', $groupId)
                ->get()->result_array();

            $response = array('status' => true, 'message' => 'Group Expenses Fetched Successfully', 'expenses' => $expensesArray);
        } catch (Exception $e) {
            $response = array('status' => false, 'message' => 'Something Went Wrong');
        }
        return $response;
    }

    function getExpenseDetails($expenseId)
    {
        try {
            $expensesArray = $this->db->select('description, amount, payer, split')
                ->from('expenses')
                ->where('expense_id', $expenseId)
                ->get()->result_array();

            $response = array('status' => true, 'message' => 'Group Expenses Fetched Successfully', 'expense' => $expensesArray[0]);
        } catch (Exception $e) {
            $response = array('status' => false, 'message' => 'Something Went Wrong');
        }
        return $response;
    }

    function getNameByMemberId($memberId)
    {
        $nameArray = $this->db->select('users.name as fullName')
            ->from('group_members')
            ->join('users', 'group_members.fk_user_id = users.user_id')
            ->where('member_id', $memberId)
            ->get()->result_array();

        return $nameArray[0]['fullName'];
    }


    function getBalances($groupId)
    {
        try {
            $membersArray = $this->db->select('group_members.member_id as memberId, users.name as fullName, group_members.total_paid as totalPaid, group_members.total_share as totalShare')
                ->from('group_members')
                ->join('users', 'group_members.fk_user_id = users.user_id')
                ->where('fk_group_id', $groupId)
                ->get()->result_array();

            $paidByArray = array();
            $needToPay = array();
            $totalToGet = 0;
            foreach ($membersArray as $memberKey => $memberValue) {
                if ($memberValue['totalPaid'] > $memberValue['totalShare']) {
                    $singleUser = array();
                    $singleUser['memberId'] = $memberValue['memberId'];
                    $singleUser['fullName'] = $memberValue['fullName'];
                    $singleUser['toGet'] = $memberValue['totalPaid'] - $memberValue['totalShare'];
                    $totalToGet += ($memberValue['totalPaid'] - $memberValue['totalShare']);
                    array_push($paidByArray, $singleUser);
                } else if ($memberValue['totalPaid'] < $memberValue['totalShare']) {
                    $singleUser = array();
                    $singleUser['memberId'] = $memberValue['memberId'];
                    $singleUser['fullName'] = $memberValue['fullName'];
                    $singleUser['toPay'] = $memberValue['totalShare'] - $memberValue['totalPaid'];
                    array_push($needToPay, $singleUser);
                }
            }

            foreach ($paidByArray as $paidMemberKey => $paidMemberValue) {
                $paidByArray[$paidMemberKey]['percent'] = ($paidMemberValue['toGet'] * 100) / $totalToGet;
            }

            foreach ($needToPay as $toPayMemberKey => $toPayMemberValue) {
                $payToUsers = array();
                foreach ($paidByArray as $paidKey => $paidValue) {
                    $payment = array();
                    $payment['memberId'] = $paidValue['memberId'];
                    $payment['fullName'] = $paidValue['fullName'];
                    $payment['amount'] = ($toPayMemberValue['toPay'] * $paidValue['percent']) / 100;
                    array_push($payToUsers, $payment);
                }
                $needToPay[$toPayMemberKey]['payToUsers'] = $payToUsers;
            }

            $response = array('status' => true, 'message' => 'Group Balances Fetched Successfully', 'hasToGet' => $paidByArray, 'needToPay' => $needToPay);
        } catch (Exception $e) {
            $response = array('status' => false, 'message' => 'Something Went Wrong');
        }
        return $response;
    }

    function getGroupsByUserId($userId)
    {
        try {
            $groupsArray = $this->db->select('groups.group_id as groupId,groups.name, groups.description, group_members.total_paid as totalPaid, group_members.total_share as totalShare')
                ->from('group_members')
                ->join('groups', 'group_members.fk_group_id = groups.group_id')
                ->where('group_members.fk_user_id', $userId)
                ->get()->result_array();

            foreach ($groupsArray as $groupKey => $groupValue) {

                if ($groupValue['totalPaid'] > $groupValue['totalShare']) {
                    $groupsArray[$groupKey]['amountToGet'] = $groupValue['totalPaid'] - $groupValue['totalShare'];
                    $groupsArray[$groupKey]['amountToPay'] = 0;
                } elseif ($groupValue['totalPaid'] < $groupValue['totalShare']) {
                    $groupsArray[$groupKey]['amountToGet'] = 0;
                    $groupsArray[$groupKey]['amountToPay'] = $groupValue['totalShare'] - $groupValue['totalPaid'];
                } else {
                    $groupsArray[$groupKey]['amountToGet'] = 0;
                    $groupsArray[$groupKey]['amountToPay'] = 0;
                }
                $groupTotalSpend = $this->db->select('SUM(group_members.total_paid) as totalSpend')
                    ->from('group_members')
                    ->where('fk_group_id', $groupValue['groupId'])
                    ->get()->result_array();

                $groupsArray[$groupKey]['totalGroupSpend'] = $groupTotalSpend[0]['totalSpend'];
            }

            $response = array('status' => true, 'message' => 'User Groups Fetched Successfully', 'groups' => $groupsArray);
        } catch (Exception $e) {
            $response = array('status' => false, 'message' => 'Something Went Wrong');
        }
        return $response;
    }

    function getUserStats($userId)
    {
        try {
            $groupsArray = $this->db->select('groups.group_id as groupId,groups.name, groups.description, group_members.total_paid as totalPaid, group_members.total_share as totalShare')
                ->from('group_members')
                ->join('groups', 'group_members.fk_group_id = groups.group_id')
                ->where('group_members.fk_user_id', $userId)
                ->get()->result_array();

            $userStats = array();
            $userStats['groupsCount'] = count($groupsArray, 0);
            $userStats['totalPaid'] = 0;
            $userStats['totalSpend'] = 0;
            $userStats['totalToPay'] = 0;
            $totalGroupsToPay = 0;
            $totalGroupsToGet = 0;
            $userStats['totalToGet'] = 0;

            foreach ($groupsArray as $groupKey => $groupValue) {
                $userStats['totalPaid'] += $groupValue['totalPaid'];
                $userStats['totalSpend'] += $groupValue['totalShare'];

                if ($groupValue['totalPaid'] > $groupValue['totalShare']) {
                    $totalGroupsToGet++;
                    $userStats['totalToGet'] += $groupValue['totalPaid'] - $groupValue['totalShare'];
                } else if ($groupValue['totalPaid'] < $groupValue['totalShare']) {
                    $totalGroupsToPay++;
                    $userStats['totalToPay'] += $groupValue['totalShare'] - $groupValue['totalPaid'];
                }
            }
            $userStats['totalGroupsToPay'] = $totalGroupsToPay;
            $userStats['totalGroupsToGet'] = $totalGroupsToGet;

            $response = array('status' => true, 'message' => 'User Stats Fetched Successfully', 'userStats' => $userStats);
        } catch (Exception $e) {
            $response = array('status' => false, 'message' => 'Something Went Wrong');
        }
        return $response;
    }
}
