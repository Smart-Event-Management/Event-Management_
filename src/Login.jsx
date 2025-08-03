import React from "react";
import { Link } from "react-router-dom";
import "./b.css";
import "boxicons/css/boxicons.min.css";

const Login = () => {
  return (
    <div className="wrapper">
      <form>
        <h1>LOGIN</h1>

        <div className="input-box">
          <input type="text" required placeholder="USERNAME" />
          <i className="bx bxs-user"></i>
        </div>

        <div className="input-box">
          <input type="password" required placeholder="PASSWORD" />
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
