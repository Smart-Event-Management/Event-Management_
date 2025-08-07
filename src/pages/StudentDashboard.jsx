// src/pages/AdminDashboard.jsx
import React from "react";
import { useLocation } from "react-router-dom";

const AdminDashboard = () => {
  const location = useLocation();
  const message = location.state?.message;

  return (
    <div style={{ minHeight: "100vh", display: "flex", flexDirection: "column", backgroundColor: "transparent", color: "white" }}>
      <div style={{ flex: 1, textAlign: "center", paddingTop: "250px" }}>
        {message && <p style={{ color: "white" }}>{message}</p>}
        <h1>👍Welcome to Student Dashboard👍</h1>
        {/* Add more content here */}
      </div>
      
      {/* Footer */}
      <footer style={{ backgroundColor: "#20232a", textAlign: "center", padding: "10px 0", marginTop: "auto" }}>
        <p style={{ color: "#61dafb", margin: 0 }}>Developed by Hirohamada,Mukesh,Himavarshini,Kushal</p>
      </footer>
    </div>
  );
};

export default AdminDashboard;
