// src/pages/AdminDashboard.jsx
import React from "react";
import { useLocation } from "react-router-dom";

const AdminDashboard = () => {
  const location = useLocation();
  const message = location.state?.message;

  return (
    <div>
      {message && <p style={{ color: "white", textAlign: "center" }}>{message}</p>}
      <h1>👍Welcome to Organizer Dashboard👍</h1>
      {/* Add more content here */}
    </div>
  );
};

export default AdminDashboard;
