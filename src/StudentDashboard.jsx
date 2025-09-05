import { useState, useEffect } from "react";
import "./StudentDashboard.css";

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
    window.addEventListener("scroll", handleScroll);
    return () => window.removeEventListener("scroll", handleScroll);
  }, []);

  return (
    <nav className={`navbar ${isScrolled ? "scrolled" : ""}`}>
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
              onClick={() => (window.location.href = "/login")}
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
  // ✅ posters state added
  const [posters, setPosters] = useState([]);

  // ✅ fetch posters from backend
  useEffect(() => {
    fetch("http://localhost/backend/fetch_posters.php")
      .then((res) => res.json())
      .then((data) => setPosters(data))
      .catch((err) => console.error("Error fetching posters:", err));
  }, []);

  return (
    <>
      <Navbar />
      <main style={{ padding: "100px 2rem 2rem 2rem" }}>
        {/* Main Content (centered) */}
        <div style={{ textAlign: "center", marginBottom: "50px" }}>
          <h1>Welcome to the Student Dashboard</h1>

          {/* Poster Scroll Section */}
          <section className="poster-scroll-section">
            <h2>Event Posters</h2>
            <div className="poster-scroll">
              {posters.length > 0 ? (
                posters.map((poster) => (
                  <div key={poster.id} className="poster-card">
                    <img
                      src={`http://localhost/backend/${poster.image}`}
                      alt={poster.title}
                      className="poster-image"
                    />
                    <p className="poster-title">{poster.title}</p>
                  </div>
                ))
              ) : (
                <p>No posters available</p>
              )}
            </div>
          </section>
        </div>

        {/* IT Section (left-aligned) */}
        <section className="it-section">
          <h2
            style={{
              fontSize: "24px",
              fontWeight: "bold",
              marginBottom: "16px",
            }}
          >
            IT
          </h2>

          {/* Poster Container with border */}
          <div className="poster-container">
            <div
              style={{
                width: "200px",
                height: "200px",
                backgroundColor: "#e2e8f0",
                borderRadius: "8px",
                overflow: "hidden",
              }}
            >
              <img
                src="https://placehold.co/200x200/a0aec0/ffffff?text=Image1"
                alt="Image1"
                style={{ width: "100%", height: "100%", objectFit: "cover" }}
              />
            </div>
            <div
              style={{
                width: "200px",
                height: "200px",
                backgroundColor: "#e2e8f0",
                borderRadius: "8px",
                overflow: "hidden",
              }}
            >
              <img
                src="https://placehold.co/200x200/a0aec0/ffffff?text=Image2"
                alt="Image2"
                style={{ width: "100%", height: "100%", objectFit: "cover" }}
              />
            </div>
          </div>
        </section>
      </main>
    </>
  );
};

export default StudentDashboard;
