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
          <a href="/" className="navbar-brand">
            Student Dashboard
          </a>
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
  const [posters] = useState([
    {
      id: 1,
      image: "/scroll/1.jpg"
    },
    {
      id: 2,
      image: "/scroll/2.jpg"
    },
    {
      id: 3,
      image: "/scroll/3.jpg"
    },
    {
      id: 4,
      image: "/scroll/4.jpg"
    },
  ]);

  const [currentIndex, setCurrentIndex] = useState(0);

  useEffect(() => {
    if (posters.length === 0) return;
    const interval = setInterval(() => {
      setCurrentIndex((prev) => (prev + 1) % posters.length);
    }, 4000); // 4 seconds delay
    return () => clearInterval(interval);
  }, [posters.length]);

  const goToNext = () => {
    setCurrentIndex((prev) => (prev + 1) % posters.length);
  };
  const goToPrevious = () => {
    setCurrentIndex((prev) => (prev - 1 + posters.length) % posters.length);
  };
  const goToSlide = (index) => {
    setCurrentIndex(index);
  };

  if (posters.length === 0) {
    return (
      <>
        <Navbar />
        <div className="loading-container">
          <p>No posters available.</p>
        </div>
      </>
    );
  }

  return (
    <>
      <Navbar />
      
      <div className="carousel-container">
        <div
          className="carousel-wrapper"
          style={{
            width: `${posters.length * 100}%`,
            transform: `translateX(-${currentIndex * (100 / posters.length)}%)`
          }}
        >
          {posters.map((poster, index) => (
            <div key={poster.id} className="carousel-slide">
              <img
                src={poster.image}
                alt={`Poster ${poster.id}`}
                className="poster-image"
              />
            </div>
          ))}
        </div>
        <button className="carousel-nav prev" onClick={goToPrevious}>
          ❮
        </button>
        <button className="carousel-nav next" onClick={goToNext}>
          ❯
        </button>
        <div className="carousel-dots">
          {posters.map((_, index) => (
            <button
              key={index}
              className={`carousel-dot ${index === currentIndex ? 'active' : ''}`}
              onClick={() => goToSlide(index)}
            />
          ))}
        </div>
      </div>
      <main>
        {/* The new "Manage Events" container */}
        <section className="event-management-container">
          <div className="event-tabs">
            <div className="event-tab active-tab">Manage Events</div>
          </div>
        </section>
      </main>
    </>
  );
};

export default StudentDashboard;