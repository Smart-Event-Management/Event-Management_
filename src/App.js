import React from 'react';
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import Login from './Login';
import SignUp from './SignUp'; // Match the actual filename
import AdminDashboard from "./AdminDashboard";
import OrganizerDashboard from "./OrganizerDashboard";
import StudentDashboard from "./StudentDashboard";

function App() {
  return (
    <Router>
      <div className="App">
        <Routes>
          <Route path="/" element={<Login />} />
          <Route path="/login" element={<Login />} />
          <Route path="/signup" element={<SignUp />} />
             <Route path="/admin-dashboard" element={<AdminDashboard />} />
        <Route path="/organizer-dashboard" element={<OrganizerDashboard />} />
          <Route path="/student-dashboard" element={<StudentDashboard />} />
        </Routes>
      </div>
    </Router>
  );
}

export default App;