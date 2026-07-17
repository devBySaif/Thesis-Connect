<?php

require_once __DIR__ . '/../config/database.php';

class User
{
    private $conn;
    private $table = "users";

  public function __construct($conn)
{
    $this->conn = $conn;
}

    /* ===============================
       Check Email Exists
    ================================ */

    public function emailExists($email)
    {
        $sql = "SELECT id FROM users WHERE email = :email LIMIT 1";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindParam(':email', $email);

        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    public function emailExistsExceptUser($email, $userId)
    {
        $sql = "SELECT id FROM users WHERE email = :email AND id != :user_id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    /* ===============================
       Register User
    ================================ */

    public function registerUser($email, $password, $role)
    {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users
                (email,password,role,is_verified)
                VALUES
                (:email,:password,:role,0)";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':role', $role);

        if($stmt->execute())
        {
            return $this->conn->lastInsertId();
        }

        return false;
    }

    public function ensureDefaultAdmin()
    {
        $defaultEmail = 'admin@thesisconnect.com';
        $defaultPassword = 'Admin@123';

        if (!$this->emailExists($defaultEmail)) {
            $hashedPassword = password_hash($defaultPassword, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (email,password,role,is_verified) VALUES (:email,:password,'admin',1)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':email', $defaultEmail);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->execute();
        }
    }

    public function countUsersByRole($role)
    {
        $sql = "SELECT COUNT(*) FROM users WHERE role = :role";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':role', $role);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public function countPendingUsersByRole($role)
    {
        $sql = "SELECT COUNT(*) FROM users WHERE role = :role AND is_verified = 0";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':role', $role);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public function createAdmin($email, $password, $fullName, $phone)
    {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        try {
            $this->conn->beginTransaction();

            $sql = "INSERT INTO users (email,password,role,is_verified) VALUES (:email,:password,'admin',1)";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':email' => $email,
                ':password' => $hashedPassword
            ]);

            $userId = $this->conn->lastInsertId();

            $profileSql = "INSERT INTO admin_profiles (user_id,full_name,phone) VALUES (:user_id,:full_name,:phone)";
            $profileStmt = $this->conn->prepare($profileSql);
            $profileStmt->execute([
                ':user_id' => $userId,
                ':full_name' => $fullName,
                ':phone' => $phone
            ]);

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function getAdmins()
    {
        $sql = "SELECT u.id AS user_id, u.email, u.is_verified, u.created_at AS user_created_at,
                       ap.full_name, ap.phone, ap.created_at AS profile_created_at
                FROM users u
                LEFT JOIN admin_profiles ap ON u.id = ap.user_id
                WHERE u.role = 'admin'
                ORDER BY u.id DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countExistingTables(array $tables)
    {
        foreach ($tables as $table) {
            try {
                $sql = "SELECT COUNT(*) FROM {$table}";
                $stmt = $this->conn->query($sql);
                return (int) $stmt->fetchColumn();
            } catch (PDOException $e) {
                continue;
            }
        }
        return 0;
    }

    public function getPendingStudents()
    {
        $sql = "SELECT u.id AS user_id, u.email, u.is_verified, sp.full_name, sp.student_id, sp.department, sp.semester, sp.cgpa, sp.phone
                FROM users u
                JOIN student_profiles sp ON u.id = sp.user_id
                WHERE u.role = 'student' AND u.is_verified = 0";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPendingTeachers()
    {
        $sql = "SELECT u.id AS user_id, u.email, u.is_verified, tp.full_name, tp.teacher_id, tp.designation, tp.department, tp.office, tp.phone
                FROM users u
                JOIN teacher_profiles tp ON u.id = tp.user_id
                WHERE u.role = 'teacher' AND u.is_verified = 0";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getVerifiedTeachers()
    {
        $sql = "SELECT u.id AS user_id, u.email, tp.full_name, tp.designation, tp.department
                FROM users u
                JOIN teacher_profiles tp ON u.id = tp.user_id
                WHERE u.role = 'teacher' AND u.is_verified = 1
                ORDER BY tp.full_name ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateUserVerification($userId, $status)
    {
        $sql = "UPDATE users SET is_verified = :status WHERE id = :user_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':status', $status, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function deleteUserById($userId)
    {
        $roleSql = "SELECT role FROM users WHERE id = :user_id LIMIT 1";
        $roleStmt = $this->conn->prepare($roleSql);
        $roleStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $roleStmt->execute();
        $role = $roleStmt->fetchColumn();

        if (!$role) {
            return false;
        }

        try {
            $this->conn->beginTransaction();

            if ($role === 'student') {
                $deleteProfile = "DELETE FROM student_profiles WHERE user_id = :user_id";
                $stmt = $this->conn->prepare($deleteProfile);
                $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
                $stmt->execute();
            }

            if ($role === 'teacher') {
                $deleteProfile = "DELETE FROM teacher_profiles WHERE user_id = :user_id";
                $stmt = $this->conn->prepare($deleteProfile);
                $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
                $stmt->execute();
            }

            $deleteUser = "DELETE FROM users WHERE id = :user_id";
            $stmt = $this->conn->prepare($deleteUser);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function getAdminProfile($userId)
    {
        $sql = "SELECT ap.* FROM admin_profiles ap JOIN users u ON ap.user_id = u.id WHERE u.id = :user_id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getStudentProfile($userId)
    {
        $sql = "SELECT u.email, u.is_verified, sp.*
                FROM users u
                JOIN student_profiles sp ON u.id = sp.user_id
                WHERE u.id = :user_id AND u.role = 'student'
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateStudentProfile($userId, array $data)
    {
        try {
            $this->conn->beginTransaction();

            $userSql = "UPDATE users SET email = :email WHERE id = :user_id AND role = 'student'";
            $userStmt = $this->conn->prepare($userSql);
            $userStmt->execute([
                ':email' => $data['email'],
                ':user_id' => $userId
            ]);

            $profileSql = "UPDATE student_profiles
                           SET full_name = :full_name,
                               student_id = :student_id,
                               department = :department,
                               semester = :semester,
                               cgpa = :cgpa,
                               phone = :phone,
                               bio = :bio";

            $params = [
                ':full_name' => $data['full_name'],
                ':student_id' => $data['student_id'],
                ':department' => $data['department'],
                ':semester' => $data['semester'],
                ':cgpa' => $data['cgpa'],
                ':phone' => $data['phone'],
                ':bio' => $data['bio'],
                ':user_id' => $userId
            ];

            if (!empty($data['profile_picture'])) {
                $profileSql .= ", profile_picture = :profile_picture";
                $params[':profile_picture'] = $data['profile_picture'];
            }

            $profileSql .= " WHERE user_id = :user_id";
            $profileStmt = $this->conn->prepare($profileSql);
            $profileStmt->execute($params);

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function getUserById($userId)
    {
        $sql = "SELECT * FROM users WHERE id = :user_id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateUserPassword($userId, $password)
    {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET password = :password WHERE id = :user_id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':password' => $hashedPassword,
            ':user_id' => $userId
        ]);
    }

    public function createRecruitmentPost(array $data)
    {
        $sql = "INSERT INTO recruitment_posts
                (student_user_id, teacher_user_id, title, description, department, members_needed, deadline, status)
                VALUES
                (:student_user_id, :teacher_user_id, :title, :description, :department, :members_needed, :deadline, 'open')";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':student_user_id' => $data['student_user_id'],
            ':teacher_user_id' => $data['teacher_user_id'] ?: null,
            ':title' => $data['title'],
            ':description' => $data['description'],
            ':department' => $data['department'],
            ':members_needed' => $data['members_needed'],
            ':deadline' => $data['deadline']
        ]);
    }

    public function updateRecruitmentPost($postId, $studentUserId, array $data)
    {
        $sql = "UPDATE recruitment_posts
                SET teacher_user_id = :teacher_user_id,
                    title = :title,
                    description = :description,
                    department = :department,
                    members_needed = :members_needed,
                    deadline = :deadline,
                    status = :status
                WHERE id = :post_id AND student_user_id = :student_user_id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':teacher_user_id' => $data['teacher_user_id'] ?: null,
            ':title' => $data['title'],
            ':description' => $data['description'],
            ':department' => $data['department'],
            ':members_needed' => $data['members_needed'],
            ':deadline' => $data['deadline'],
            ':status' => $data['status'],
            ':post_id' => $postId,
            ':student_user_id' => $studentUserId
        ]);
    }

    public function deleteRecruitmentPost($postId, $studentUserId)
    {
        try {
            $this->conn->beginTransaction();

            $deleteApplications = "DELETE pa
                                   FROM post_applications pa
                                   JOIN recruitment_posts rp ON pa.post_id = rp.id
                                   WHERE pa.post_id = :post_id AND rp.student_user_id = :student_user_id";
            $stmt = $this->conn->prepare($deleteApplications);
            $stmt->execute([
                ':post_id' => $postId,
                ':student_user_id' => $studentUserId
            ]);

            $deletePost = "DELETE FROM recruitment_posts WHERE id = :post_id AND student_user_id = :student_user_id";
            $stmt = $this->conn->prepare($deletePost);
            $stmt->execute([
                ':post_id' => $postId,
                ':student_user_id' => $studentUserId
            ]);

            $deleted = $stmt->rowCount() > 0;
            $this->conn->commit();
            return $deleted;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function getRecruitmentPostById($postId)
    {
        $sql = "SELECT rp.*, sp.full_name AS owner_name, sp.profile_picture AS owner_picture,
                       tp.full_name AS teacher_name
                FROM recruitment_posts rp
                JOIN student_profiles sp ON rp.student_user_id = sp.user_id
                LEFT JOIN teacher_profiles tp ON rp.teacher_user_id = tp.user_id
                WHERE rp.id = :post_id
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':post_id', $postId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getRecentRecruitmentPosts($viewerUserId = 0, $limit = 20)
    {
        $sql = "SELECT rp.*, sp.full_name AS owner_name, sp.profile_picture AS owner_picture,
                       tp.full_name AS teacher_name,
                       COUNT(pa.id) AS apply_count,
                       my_app.status AS my_status
                FROM recruitment_posts rp
                JOIN student_profiles sp ON rp.student_user_id = sp.user_id
                LEFT JOIN teacher_profiles tp ON rp.teacher_user_id = tp.user_id
                LEFT JOIN post_applications pa ON rp.id = pa.post_id
                LEFT JOIN post_applications my_app
                    ON rp.id = my_app.post_id AND my_app.applicant_user_id = :viewer_user_id
                GROUP BY rp.id, my_app.status
                ORDER BY rp.created_at DESC
                LIMIT {$limit}";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':viewer_user_id', $viewerUserId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMyRecruitmentPosts($studentUserId)
    {
        $sql = "SELECT rp.*, tp.full_name AS teacher_name, COUNT(pa.id) AS apply_count
                FROM recruitment_posts rp
                LEFT JOIN teacher_profiles tp ON rp.teacher_user_id = tp.user_id
                LEFT JOIN post_applications pa ON rp.id = pa.post_id
                WHERE rp.student_user_id = :student_user_id
                GROUP BY rp.id
                ORDER BY rp.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':student_user_id', $studentUserId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function applyToRecruitmentPost($postId, $applicantUserId, $message)
    {
        $post = $this->getRecruitmentPostById($postId);
        if (!$post || (int) $post['student_user_id'] === (int) $applicantUserId || strtotime($post['deadline']) < strtotime(date('Y-m-d'))) {
            return false;
        }

        $sql = "INSERT INTO post_applications (post_id, applicant_user_id, message)
                VALUES (:post_id, :applicant_user_id, :message)";
        $stmt = $this->conn->prepare($sql);
        try {
            return $stmt->execute([
                ':post_id' => $postId,
                ':applicant_user_id' => $applicantUserId,
                ':message' => $message
            ]);
        } catch (Exception $e) {
            return false;
        }
    }

    public function getApplicationForOwner($applicationId, $ownerUserId)
    {
        $sql = "SELECT pa.*, rp.title AS post_title, rp.id AS post_id, rp.student_user_id,
                       sp.full_name AS applicant_name
                FROM post_applications pa
                JOIN recruitment_posts rp ON pa.post_id = rp.id
                JOIN student_profiles sp ON pa.applicant_user_id = sp.user_id
                WHERE pa.id = :application_id AND rp.student_user_id = :owner_user_id
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':application_id' => $applicationId,
            ':owner_user_id' => $ownerUserId
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getApplicationsForOwner($ownerUserId)
    {
        $sql = "SELECT pa.*, rp.title AS post_title, rp.id AS post_id,
                       u.email,
                       sp.full_name, sp.student_id, sp.department, sp.semester, sp.cgpa, sp.phone, sp.profile_picture, sp.bio
                FROM post_applications pa
                JOIN recruitment_posts rp ON pa.post_id = rp.id
                JOIN users u ON pa.applicant_user_id = u.id
                JOIN student_profiles sp ON pa.applicant_user_id = sp.user_id
                WHERE rp.student_user_id = :owner_user_id
                ORDER BY pa.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':owner_user_id', $ownerUserId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getApplicationsForPost($postId, $ownerUserId)
    {
        $sql = "SELECT pa.*, u.email,
                       sp.full_name, sp.student_id, sp.department, sp.semester, sp.cgpa, sp.phone, sp.profile_picture, sp.bio
                FROM post_applications pa
                JOIN recruitment_posts rp ON pa.post_id = rp.id
                JOIN users u ON pa.applicant_user_id = u.id
                JOIN student_profiles sp ON pa.applicant_user_id = sp.user_id
                WHERE pa.post_id = :post_id AND rp.student_user_id = :owner_user_id
                ORDER BY pa.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':post_id' => $postId,
            ':owner_user_id' => $ownerUserId
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateApplicationStatus($applicationId, $ownerUserId, $status)
    {
        $sql = "UPDATE post_applications pa
                JOIN recruitment_posts rp ON pa.post_id = rp.id
                SET pa.status = :status, pa.is_seen = 1
                WHERE pa.id = :application_id AND rp.student_user_id = :owner_user_id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':status' => $status,
            ':application_id' => $applicationId,
            ':owner_user_id' => $ownerUserId
        ]);
    }

    public function countUnseenApplicationsForOwner($ownerUserId)
    {
        $sql = "SELECT COUNT(*)
                FROM post_applications pa
                JOIN recruitment_posts rp ON pa.post_id = rp.id
                WHERE rp.student_user_id = :owner_user_id AND pa.is_seen = 0";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':owner_user_id', $ownerUserId, PDO::PARAM_INT);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public function markApplicationsSeenForOwner($ownerUserId)
    {
        $sql = "UPDATE post_applications pa
                JOIN recruitment_posts rp ON pa.post_id = rp.id
                SET pa.is_seen = 1
                WHERE rp.student_user_id = :owner_user_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':owner_user_id', $ownerUserId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function createAnnouncement($adminUserId, $title, $body)
    {
        $sql = "INSERT INTO announcements (admin_user_id, title, body)
                VALUES (:admin_user_id, :title, :body)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':admin_user_id' => $adminUserId,
            ':title' => $title,
            ':body' => $body
        ]);
    }

    public function getAnnouncements($limit = 20)
    {
        $sql = "SELECT a.*, u.email AS admin_email
                FROM announcements a
                JOIN users u ON a.admin_user_id = u.id
                ORDER BY a.created_at DESC
                LIMIT {$limit}";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createNotification($userId, $title, $body, $linkUrl)
    {
        $sql = "INSERT INTO notifications (user_id, title, body, link_url)
                VALUES (:user_id, :title, :body, :link_url)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':user_id' => $userId,
            ':title' => $title,
            ':body' => $body,
            ':link_url' => $linkUrl
        ]);
    }

    public function getNotificationsForUser($userId, $limit = 8)
    {
        $sql = "SELECT *
                FROM notifications
                WHERE user_id = :user_id
                ORDER BY is_read ASC, created_at DESC
                LIMIT {$limit}";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countUnreadNotifications($userId)
    {
        $sql = "SELECT COUNT(*) FROM notifications WHERE user_id = :user_id AND is_read = 0";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public function readNotification($notificationId, $userId)
    {
        $sql = "SELECT link_url FROM notifications WHERE id = :notification_id AND user_id = :user_id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':notification_id' => $notificationId,
            ':user_id' => $userId
        ]);
        $linkUrl = $stmt->fetchColumn();

        if (!$linkUrl) {
            return false;
        }

        $updateSql = "UPDATE notifications SET is_read = 1 WHERE id = :notification_id AND user_id = :user_id";
        $updateStmt = $this->conn->prepare($updateSql);
        $updateStmt->execute([
            ':notification_id' => $notificationId,
            ':user_id' => $userId
        ]);

        return $linkUrl;
    }

    /* ===============================
       Insert Student Profile
    ================================ */

    public function insertStudentProfile($data)
    {
        $sql = "INSERT INTO student_profiles
        (user_id,full_name,student_id,department,semester,cgpa,phone,profile_picture,bio)

        VALUES

        (:user_id,:full_name,:student_id,:department,:semester,:cgpa,:phone,:profile_picture,:bio)";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([

            ':user_id'=>$data['user_id'],

            ':full_name'=>$data['full_name'],

            ':student_id'=>$data['student_id'],

            ':department'=>$data['department'],

            ':semester'=>$data['semester'],

            ':cgpa'=>$data['cgpa'],

            ':phone'=>$data['phone'],

            ':profile_picture'=>$data['profile_picture'],

            ':bio'=>$data['bio']

        ]);
    }

    /* ===============================
       Insert Teacher Profile
    ================================ */

    public function insertTeacherProfile($data)
    {

        $sql = "INSERT INTO teacher_profiles
        (user_id,full_name,teacher_id,designation,department,office,phone,profile_picture,bio)

        VALUES

        (:user_id,:full_name,:teacher_id,:designation,:department,:office,:phone,:profile_picture,:bio)";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([

            ':user_id'=>$data['user_id'],

            ':full_name'=>$data['full_name'],

            ':teacher_id'=>$data['teacher_id'],

            ':designation'=>$data['designation'],

            ':department'=>$data['department'],

            ':office'=>$data['office'],

            ':phone'=>$data['phone'],

            ':profile_picture'=>$data['profile_picture'],

            ':bio'=>$data['bio']

        ]);

    }

    /* ===============================
       Login
    ================================ */

    public function login($email)
    {

        $sql = "SELECT * FROM users WHERE email=:email LIMIT 1";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindParam(':email',$email);

        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);

    }

}
