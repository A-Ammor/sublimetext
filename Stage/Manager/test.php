<?php
require_once 'Db.php';

$person_id = 2;
$department_selected = 0;
$list = array();

$stmt = $pdo->prepare('SELECT DISTINCT COUNT(DISTINCT rc.id_criteria) as criteria
                    FROM givefeedback gf INNER JOIN person_cv cv on cv.id = ?
                    INNER JOIN period o ON o.id = cv.id
                    INNER JOIN role_profile rp on rp.role_id = o.role_id 
                    INNER JOIN role_competencecriteria rc on rc.id_role = o.role_id
                    WHERE period_date_till IS NULL');
$stmt->execute(array($person_id));
$result_max_behaviour = $stmt->fetch();

if ($department_selected == 0) {
    $stmt = $pdo->prepare('SELECT DISTINCT cv.id, cv.first_name, cv.prefix, cv.last_name, r.name as role_name, d.department_name FROM person_cv cv
INNER JOIN period o ON o.id = cv.id
INNER JOIN role_profile r ON r.role_id = o.role_id
INNER JOIN department d ON d.department_id = cv.department_id
INNER JOIN givefeedback gf
WHERE cv.id != ? AND gf.id_user IS NOT NULL AND gf.id_user = ? AND gf.id_invitee = cv.id AND o.period_date_till IS NULL GROUP BY cv.id');
    $stmt->execute(array($person_id, $person_id));
    $result_invited_info = $stmt->fetchAll();

    foreach ($result_invited_info as $row) {
        $invitee = $row['id'];
        $first_name = $row['first_name'];
        $prefix = $row['prefix'];
        $last_name = $row['last_name'];
        $department_name = $row['department_name'];
        $role_name = $row['role_name'];

        $stmt = $pdo->prepare("SELECT DISTINCT sum(case when fr.level is not null then 1 else 0 end) as scored ,sum(case when fr.improvement_remark <> '' then 1 else 0 end) as improve,
                    sum(case when fr.appreciation_remark <> '' then 1 else 0 end) as appreciation  FROM role_competencecriteria rc
                    INNER JOIN feedback_rel fr on fr.id_criteria = rc.id_criteria
                    INNER JOIN period o on o.id = fr.id_invitee
							WHERE rc.id_role = o.role_id AND fr.id_invitee = ? AND o.period_date_till IS NULL AND fr.period_id = o.period_id AND fr.id_person = ?");
        $stmt->execute(array($person_id, $invitee));
        $result_invited_invitee = $stmt->fetch();

        $person = array(
            'id' => $invitee,
            'first_name' => $first_name,
            'prefix' => $prefix,
            'last_name' => $last_name,
            'department_name' => $department_name,
            'role_name' => $role_name,
            'improve' => $result_invited_invitee['improve'],
            'appreciation' => $result_invited_invitee['appreciation'],
            'scored' => $result_invited_invitee['scored']);

        array_push($list, $person);
    }

    $stmt = $pdo->prepare('SELECT p.first_name, p.last_name, p.prefix, d.department_name, r.* FROM person_cv p
                                INNER JOIN period o ON p.id = o.id
                                INNER JOIN role_profile r ON o.role_id = r.role_id
                                INNER JOIN department d ON d.department_id = p.department_id
                                WHERE p.id = ? AND o.period_date_till IS NULL');
    $stmt->execute(array($person_id));
    $result_person = $stmt->fetch();

    $stmt = $pdo->prepare("SELECT DISTINCT sum(case when fr.level is not null then 1 else 0 end) as scored ,sum(case when fr.improvement_remark <> '' then 1 else 0 end) as improve,
                    sum(case when fr.appreciation_remark <> '' then 1 else 0 end) as appreciation  FROM role_competencecriteria rc
                    INNER JOIN feedback_mng_rel fr on fr.id_criteria = rc.id_criteria
                    INNER JOIN period o on o.id = fr.id_invitee
							WHERE rc.id_role = o.role_id AND fr.id_invitee = ? AND o.period_date_till IS NULL AND fr.period_id = o.period_id");
    $stmt->execute(array($person_id));
    $result_invited_manager = $stmt->fetch();

    if ($result_invited_manager['scored'] != 0) {
        $person = array(
            'id' => "mng",
            'first_name' => "",
            'prefix' => "",
            'last_name' => "",
            'department_name' => $result_person['department_name'],
            'role_name' => "Manager",
            'improve' => $result_invited_manager['improve'],
            'appreciation' => $result_invited_manager['appreciation'],
            'scored' => $result_invited_manager['scored']);

        array_push($list, $person);
    }
} else {
    $stmt = $pdo->prepare('SELECT DISTINCT cv.id, cv.first_name, cv.prefix, cv.last_name, r.name as role_name, d.department_name,
sum(DISTINCT case when gf.id_user is not null and gf.id_user = ? and gf.id_invitee = cv.id then 1 else 0 end) as invited
FROM person_cv cv
INNER JOIN period o ON o.id = cv.id
INNER JOIN role_profile r ON r.role_id = o.role_id
INNER JOIN department d ON d.department_id = cv.department_id
INNER JOIN givefeedback gf
WHERE cv.id != ? AND cv.department_id = ?
AND o.period_date_till IS NULL GROUP BY cv.id');
    $stmt->execute(array($person_id, $person_id, $department_selected));
    $result_invited_info = $stmt->fetchAll();

    foreach ($result_invited_info as $row) {
        if ($row['invited'] == 0) {
            $invitee = $row['id'];
            $first_name = $row['first_name'];
            $prefix = $row['prefix'];
            $last_name = $row['last_name'];
            $department_name = $row['department_name'];
            $role_name = $row['role_name'];

            $stmt = $pdo->prepare("SELECT DISTINCT sum(case when fr.level is not null then 1 else 0 end) as scored ,sum(case when fr.improvement_remark <> '' then 1 else 0 end) as improve,
                    sum(case when fr.appreciation_remark <> '' then 1 else 0 end) as appreciation  FROM role_competencecriteria rc
                    INNER JOIN feedback_rel fr on fr.id_criteria = rc.id_criteria
                    INNER JOIN period o on o.id = fr.id_invitee
							WHERE rc.id_role = o.role_id AND fr.id_invitee = ? AND o.period_date_till IS NULL AND fr.period_id = o.period_id AND fr.id_person = ?");
            $stmt->execute(array($person_id, $invitee));
            $result_invited_invitee = $stmt->fetch();

            $person = array(
                'id' => $invitee,
                'first_name' => $first_name,
                'prefix' => $prefix,
                'last_name' => $last_name,
                'department_name' => $department_name,
                'role_name' => $role_name,
                'improve' => $result_invited_invitee['improve'],
                'appreciation' => $result_invited_invitee['appreciation'],
                'scored' => $result_invited_invitee['scored']);

            array_push($list, $person);
        }

        $stmt = $pdo->prepare('SELECT p.first_name, p.last_name, p.prefix, d.department_name, r.* FROM person_cv p
                                INNER JOIN period o ON p.id = o.id
                                INNER JOIN role_profile r ON o.role_id = r.role_id
                                INNER JOIN department d ON d.department_id = p.department_id
                                WHERE p.id = ? AND o.period_date_till IS NULL');
        $stmt->execute(array($person_id));
        $result_person = $stmt->fetch();
    }
}
?>
<div class="table-responsive">
    <table id="data-table-received" class="table table-striped">
        <thead>
        <tr>
            <th data-column-id="profile" data-formatter="pic" data-sortable="false">Profile</th>
            <th data-column-id="percentage" data-order="desc">Percentage</th>
            <th data-column-id="scored" data-type="numeric">Scored</th>
            <th data-column-id="above" data-type="numeric">Above</th>
            <th data-column-id="improve" data-type="numeric">Improve</th>
            <th data-column-id="person">Name</th>
            <th data-column-id="role">Role</th>
            <th data-column-id="department">Department</th>
            <th data-column-id="commands" data-formatter="commands" data-sortable="false">Commands</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($list as $row) {
            if (strlen($row['prefix']) == 0) {
                $full_name = $row['first_name'] . " " . $row['last_name'];
            } else {
                $full_name = $row['first_name'] . " " . $row['prefix'] . " " . $row['last_name'];
            } ?>
            <tr>
                <td class="hidden"><?php
                    if ($row['id'] == "mng") {
                        echo 'manager.png';
                    } else if ($row['id'] == "guest") {
                        echo 'guest.png';
                    } else {
                        echo $row['id'] . '.jpeg';
                        echo 'https://softwareguardian.eu/talentpass/avatars/' . $row['id'] . '.jpeg';
                    }
                    ?></td>
                <td><?php echo round((($row['scored'] / $result_max_behaviour['criteria']) * 100), 2) ?>%</td>
                <td><?php echo $row['scored'] ?></td>
                <td><?php echo $row['appreciation'] ?></td>
                <td><?php echo $row['improve'] ?></td>
                <td><?php echo $full_name ?></td>
                <td><?php echo $row['role_name'] ?></td>
                <td><?php echo $row['department_name'] ?></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>
