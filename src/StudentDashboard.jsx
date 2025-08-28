import { useState, useEffect } from 'react';
import './StudentDashboard.css';

const NavLink = ({ href, children }) => (
  <a href={href} className="nav-link" tabIndex="0">
    {children}
    <span className="nav-link-underline"></span>
  </a>
);

const Navbar = () => {
  const [isScrolled, setIsScrolled] = useState(false);

  useEffect(() => {
    const handleScroll = () => setIsScrolled(window.scrollY > 10);
    window.addEventListener('scroll', handleScroll);
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);

  return (
    <nav className={`navbar ${isScrolled ? 'scrolled' : ''}`}>
      <div className="navbar-container">
        <div className="navbar-content">
          {/* Left: Logo */}
          <a href="/" className="navbar-brand">
            MySite
          </a>

          {/* Right: Links */}
          <div className="nav-links">
            <NavLink href="/">Home</NavLink>
            <NavLink href="/about">About</NavLink>
            <NavLink href="/contact">Contact</NavLink>
            <button
              onClick={() => (window.location.href = '/login')}
              className="login-button"
            >
              <span className="login-button-text">Login</span>
              <div className="login-button-sheen"></div>
              <div className="login-button-glow"></div>
            </button>
          </div>
        </div>
      </div>
    </nav>
  );
};

const StudentDashboard = () => {
  return (
    <>
      <Navbar />
      <main style={{ padding: '100px 2rem 2rem 2rem', minHeight: '200vh' }}>
        <h1>Welcome to the Student Dashboard</h1>
        <p>This is your main content area. The navbar above is styled with standard CSS.</p>
        <p>Scroll down to see the navbar shrink.</p>
      </main>
    </>
  );
};

export default StudentDashboard;
