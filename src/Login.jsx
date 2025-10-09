import React, { useState } from "react";
import { Link, useNavigate } from "react-router-dom";
import "./b.css";
import "boxicons/css/boxicons.min.css";

// --- UPDATED ForgotPasswordModal Component ---
const ForgotPasswordModal = ({ onClose }) => {
  const [username, setUsername] = useState("");
  const [newPassword, setNewPassword] = useState("");
  const [confirmPassword, setConfirmPassword] = useState("");
  const [role, setRole] = useState(null);
  const [showPassword, setShowPassword] = useState(false);
  const [message, setMessage] = useState("");
  const [isLoading, setIsLoading] = useState(false);
  const [isSuccess, setIsSuccess] = useState(false);

  const handleRoleClick = (selectedRole) => {
    setRole(selectedRole);
    setMessage("");
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setMessage("");
    setIsSuccess(false);

    if (!role || !username || !newPassword || !confirmPassword) {
      setMessage("All fields are required.");
      return;
    }

    if (newPassword !== confirmPassword) {
      setMessage("Passwords do not match.");
      return;
    }

    setIsLoading(true);

    try {
      const response = await fetch(
        "http://localhost/smart/reset_password.php",
        {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ username, role, newPassword }),
        }
      );

      const result = await response.json();

      if (result.success) {
        setIsSuccess(true);
        setMessage(result.message);
      } else {
        setMessage(result.error || "An unknown error occurred.");
      }
    } catch (error) {
      setMessage("Server error. Please try again later.");
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className="modal-overlay auth-page">
      <div className="wrapper" style={{ animation: "fadeIn 0.3s ease-out" }}>
        {isSuccess ? (
          <div className="success-view">
            <h1>Success!</h1>
            <p className="success-text">{message}</p>
            <button type="button" className="btn" onClick={onClose}>
              Close
            </button>
          </div>
        ) : (
          <form onSubmit={handleSubmit}>
            <button
              type="button"
              className="modal-close-button"
              onClick={onClose}
            >
              &times;
            </button>

            <div className="form-header">
              <h1>Reset Password</h1>
            </div>

            <div className="Event-login modal-roles">
              {["Organizer", "Student"].map((r) => (
                <div className="role-option" key={r}>
                  <button
                    type="button"
                    className={`Event-btn ${r.toLowerCase()} ${
                      role === r ? "selected" : ""
                    }`}
                    onClick={() => handleRoleClick(r)}
                  >
                    {r}
                  </button>
                </div>
              ))}
            </div>

            <div className="input-box">
              <input
                type="text"
                required
                placeholder="USERNAME"
                value={username}
                onChange={(e) => setUsername(e.target.value)}
              />
              <i className="bx bxs-user"></i>
            </div>
            <div className="input-box">
              <input
                type={showPassword ? "text" : "password"}
                required
                placeholder="NEW PASSWORD"
                value={newPassword}
                onChange={(e) => setNewPassword(e.target.value)}
              />
              <i className="bx bxs-lock-alt"></i>
            </div>
            <div className="input-box">
              <input
                type={showPassword ? "text" : "password"}
                required
                placeholder="CONFIRM PASSWORD"
                value={confirmPassword}
                onChange={(e) => setConfirmPassword(e.target.value)}
              />
              <i className="bx bxs-lock"></i>
            </div>

            <div className="show-password-container">
              <label>
                <input
                  type="checkbox"
                  checked={showPassword}
                  onChange={() => setShowPassword(!showPassword)}
                />{" "}
                Show Password
              </label>
            </div>

            <button className="btn" type="submit" disabled={isLoading}>
              {isLoading ? "Resetting..." : "Reset Password"}
            </button>

            {message && (
              <p
                style={{ textAlign: "center", color: "red", marginTop: "1rem" }}
              >
                {message}
              </p>
            )}
          </form>
        )}
      </div>
    </div>
  );
};

// --- Login Component ---
const Login = () => {
  const [selectedRole, setSelectedRole] = useState(null);
  const [username, setUsername] = useState("");
  const [password, setPassword] = useState("");
  const [message, setMessage] = useState("");
  const [isForgotPasswordOpen, setIsForgotPasswordOpen] = useState(false);
  const [showPassword, setShowPassword] = useState(false);

  const navigate = useNavigate();

  const handleRoleClick = (role) => {
    setSelectedRole(role);
    setMessage("");
  };

  const handleSubmit = async (e) => {
    e.preventDefault();

    if (!selectedRole || !username || !password) {
      setMessage("Please fill all fields.");
      return;
    }

    try {
      const response = await fetch("http://localhost/smart/login.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ username, password, role: selectedRole }),
      });

      const result = await response.json();

      if (result.success) {
        const { userId, userName, role } = result.userData;
        localStorage.setItem("userId", userId);
        localStorage.setItem("userRole", role);
        localStorage.setItem("studentName", userName);

        if (selectedRole === "Admin") navigate("/admin-dashboard");
        else if (selectedRole === "Organizer") navigate("/organizer-dashboard");
        else if (selectedRole === "Student") navigate("/student-dashboard");
      } else {
        setMessage(result.message);
      }
    } catch (error) {
      setMessage("Server error. Please try again later.");
    }
  };

  return (
    <>
      <div className="auth-page">
        <div className="wrapper">
          <form onSubmit={handleSubmit}>
            <h1>LOGIN</h1>
            <br />

            <div className="Event-login">
              <div className="role-option">
                <button
                  type="button"
                  className={`Event-btn admin ${
                    selectedRole === "Admin" ? "selected" : ""
                  }`}
                  onClick={() => handleRoleClick("Admin")}
                >
                  Admin
                </button>
              </div>
              <div className="role-option">
                <button
                  type="button"
                  className={`Event-btn organizer ${
                    selectedRole === "Organizer" ? "selected" : ""
                  }`}
                  onClick={() => handleRoleClick("Organizer")}
                >
                  Organizer
                </button>
              </div>
              <div className="role-option">
                <button
                  type="button"
                  className={`Event-btn student ${
                    selectedRole === "Student" ? "selected" : ""
                  }`}
                  onClick={() => handleRoleClick("Student")}
                >
                  Student
                </button>
              </div>
            </div>

            <div className="input-box">
              <input
                type="text"
                required
                placeholder="USERNAME"
                value={username}
                onChange={(e) => setUsername(e.target.value)}
              />
              <i className="bx bxs-user"></i>
            </div>

            <div className="input-box">
              <input
                type={showPassword ? "text" : "password"}
                required
                placeholder="PASSWORD"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
              />
              <i className="bx bxs-lock"></i>
            </div>

            <div className="remember-forgot">
              <label>
                <input
                  type="checkbox"
                  checked={showPassword}
                  onChange={() => setShowPassword(!showPassword)}
                />{" "}
                Show Password
              </label>
              <button
                type="button"
                className="link-button"
                onClick={() => setIsForgotPasswordOpen(true)}
              >
                Forgot password
              </button>
            </div>

            <button className="btn" type="submit">
              Login
            </button>

            {message && (
              <p style={{ textAlign: "center", color: "red" }}>{message}</p>
            )}

            <div className="register">
              <p>
                Don't Have an Account? <Link to="/signup">Register</Link>
              </p>
            </div>
          </form>
        </div>
      </div>

      {isForgotPasswordOpen && (
        <ForgotPasswordModal onClose={() => setIsForgotPasswordOpen(false)} />
      )}
    </>
  );
};

export default Login;
