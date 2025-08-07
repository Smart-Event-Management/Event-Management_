import React, { useState } from "react";
import { Link, useNavigate } from "react-router-dom"; // ⬅️ Import useNavigate
import "./b.css";
import "boxicons/css/boxicons.min.css";

const Login = () => {
  const [selectedRole, setSelectedRole] = useState(null);
  const [username, setUsername] = useState("");
  const [password, setPassword] = useState("");
  const [message, setMessage] = useState("");

  const navigate = useNavigate(); // ⬅️ Initialize navigate

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
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          username,
          password,
          role: selectedRole,
        }),
      });

      const result = await response.json();
      setMessage(result.message);

      if (result.success) {
        // Redirect based on selectedRole
        if (selectedRole === "Admin") {
          navigate("/admin-dashboard");
        } else if (selectedRole === "Organizer") {
          navigate("/organizer-dashboard");
        } else if (selectedRole === "Student") {
          navigate("/student-dashboard");
        }
      }
    } catch (error) {
      setMessage("Server error. Please try again later.");
    }
  };

  return (
    <div className="wrapper">
      <form onSubmit={handleSubmit}>
        <h1>LOGIN</h1>
        <br />

        <div className="Event-login">
          <button type="button" className="Event-btn admin" onClick={() => handleRoleClick("Admin")}>
            Admin
          </button>
          <button type="button" className="Event-btn organizer" onClick={() => handleRoleClick("Organizer")}>
            Organizer
          </button>
          <button type="button" className="Event-btn student" onClick={() => handleRoleClick("Student")}>
            Student
          </button>
        </div>

        {selectedRole && <h2 style={{ textAlign: "center" }}>{selectedRole}</h2>}

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
            type="password"
            required
            placeholder="PASSWORD"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
          />
          <i className="bx bxs-lock"></i>
        </div>

        <div className="remember-forgot">
          <label>
            <input type="checkbox" /> Remember Me
          </label>
          <Link to="#">Forgot password</Link>
        </div>

        <button className="btn" type="submit">
          Login
        </button>

        {message && <p style={{ textAlign: "center", color: "red" }}>{message}</p>}

        <div className="register">
          <p>
            Don't Have an Account? <Link to="/signup">Register</Link>
          </p>
        </div>
      </form>
    </div>
  );
};

export default Login;
