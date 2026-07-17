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
