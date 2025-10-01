import { useState, useEffect, useRef } from "react";
import "./StudentDashboard.css";

const NavLink = ({ href, children }) => (
  <a href={href} className="nav-link" tabIndex="0">
    {children}
    <span className="nav-link-underline"></span>
  </a>
);

// --- START NEW NAVBAR COMPONENT ---
const Navbar = () => {
  const [isScrolled, setIsScrolled] = useState(false);
  const [studentName, setStudentName] = useState('');
  const [isDropdownOpen, setIsDropdownOpen] = useState(false);
  
  // Get student name from localStorage on component mount
  useEffect(() => {
    // CRITICAL CHANGE: Read the name directly from localStorage, not from a separate fetch.
    const name = localStorage.getItem('studentName');
    if (name) {
      setStudentName(name);
    }
  }, []);

  useEffect(() => {
    const handleScroll = () => setIsScrolled(window.scrollY > 10);
    window.addEventListener("scroll", handleScroll);
    return () => window.removeEventListener("scroll", handleScroll);
  }, []);

  const handleSignOut = () => {
    localStorage.removeItem('studentRollNo');
    localStorage.removeItem('studentName');
    window.location.href = "/login";
  };
  
  const toggleDropdown = () => {
      setIsDropdownOpen(prev => !prev);
  };

  return (
    <nav className={`navbar ${isScrolled ? "scrolled" : ""}`}>
      <div className="navbar-container">
        <div className="navbar-content">
          <a href="/" className="navbar-brand">
            STUDENT DASHBOARD
          </a>
          <div className="nav-links">
            <NavLink href="/student-dashboard">Home</NavLink>
            <NavLink href="https://rvrjcce.ac.in/">About</NavLink>
            <NavLink href="https://rvrjcce.ac.in/xfeedback.php">Contact</NavLink>
            
            {studentName ? (
              // Profile Dropdown Button
              <div className="profile-dropdown-container">
                <button
                  onClick={toggleDropdown}
                  className="profile-button"
                >
                  <span className="profile-name">{studentName}</span>
                  <i className={`bx bx-chevron-down dropdown-arrow ${isDropdownOpen ? 'open' : ''}`}></i>
                </button>
                
                {isDropdownOpen && (
                  <div className="dropdown-menu">
                    <button onClick={handleSignOut} className="dropdown-item">
                      <i className="bx bx-log-out"></i> SIGN OUT
                    </button>
                  </div>
                )}
              </div>
            ) : (
              // Default Login Button
              <button
                onClick={() => (window.location.href = "/login")}
                className="login-button"
              >
                <span className="login-button-text">LOGIN</span>
                <div className="login-button-sheen"></div>
                <div className="login-button-glow"></div>
              </button>
            )}
          </div>
        </div>
      </div>
    </nav>
  );
};
// --- END NEW NAVBAR COMPONENT ---


// Modal component to display event details
const EventDetailsModal = ({ event, onClose }) => {
  if (!event) return null;

  return (
    <div className="modal-overlay" onClick={onClose}>
      <div className="modal-content" onClick={(e) => e.stopPropagation()}>
        <button className="modal-close-button" onClick={onClose}>
          &times;
        </button>
        <div className="modal-header">
          <img src={`/posters/${event.poster}`} alt={event.eventName} className="modal-poster" />
          <h2 className="modal-title">{event.eventName}</h2>
        </div>
        <div className="modal-body">
          <p>
            <strong>Department:</strong> {event.department}
          </p>
          <p>
            <strong>Date:</strong> {event.date}
          </p>
          <p>
            <strong>Time:</strong> {event.time}
          </p>
          <p>
            <strong>Venue:</strong> {event.venue}
          </p>
          {event.link && (
            <p>
              <strong>Event Link:</strong> <a href={event.link} target="_blank" rel="noopener noreferrer">{event.link}</a>
            </p>
          )}
          {event.First_prizes && <p><strong>1st Prize:</strong> {event.First_prizes}</p>}
          {event.Second_prizes && <p><strong>2nd Prize:</strong> {event.Second_prizes}</p>}
          {event.Third_prizes && <p><strong>3rd Prize:</strong> {event.Third_prizes}</p>}
        </div>
      </div>
    </div>
  );
};

const ManageEvents = () => {
  const [departments, setDepartments] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [selectedEvent, setSelectedEvent] = useState(null);
  const scrollContainerRefs = useRef({});

  const handleScroll = (scrollAmount, departmentName) => {
    const container = scrollContainerRefs.current[departmentName];
    if (container) {
      container.scrollLeft += scrollAmount;
    }
  };

  const openEventModal = async (eventId) => {
    try {
      const response = await fetch(`http://localhost/smart/poster-button-click.php?id=${eventId}`);
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      const data = await response.json();
      if (data.success) {
        setSelectedEvent(data.data);
      } else {
        setError(data.message);
      }
    } catch (e) {
      setError("Failed to fetch event details. Please try again.");
    }
  };

  const closeEventModal = () => {
    setSelectedEvent(null);
  };

  useEffect(() => {
    const fetchPosters = async () => {
      try {
        const response = await fetch("http://localhost/smart/deptposters.php");
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        const data = await response.json();
        setDepartments(data.departments);
      } catch (e) {
        setError(
          "Failed to load department posters. Please check the backend connection."
        );
        console.error("Fetching error: ", e);
      } finally {
        setLoading(false);
      }
    };
    fetchPosters();
  }, []);

  return (
    <>
      <section className="event-management-container">
        <div className="event-tabs">
          <div className="event-tab active-tab">ACTIVE EVENTS</div>
        </div>

        {loading && <div className="loading-state">Loading department posters...</div>}
        
        {error && <div className="error-state">{error}</div>}

        {!loading && !error && (
          <div className="department-posters-container">
            {departments.map((department) => (
              <div key={department.department_name} className="department-section">
                <h2 className="department-title">{department.department_name}</h2>
                <div className="poster-scroll-wrapper">
                  <button
                    className="scroll-button left"
                    onClick={() => handleScroll(-300, department.department_name)}
                  >
                    ❮
                  </button>
                  <div
                    className="poster-scroll-container"
                    ref={(el) => (scrollContainerRefs.current[department.department_name] = el)}
                  >
                    {department.events.length > 0 ? (
                      department.events.map((event) => (
                        <div key={event.id} className="poster-item" onClick={() => openEventModal(event.id)}>
                          <img
                            src={`/posters/${event.poster_name}`}
                            alt={event.event_name}
                            className="department-poster-image"
                            loading="lazy" // <--- ADD THIS ATTRIBUTE
                          />
                          <p className="poster-caption">{event.event_name}</p>
                        </div>
                      ))
                    ) : (
                      <p className="no-posters-message">
                        No posters available for this department.
                      </p>
                    )}
                  </div>
                  <button
                    className="scroll-button right"
                    onClick={() => handleScroll(300, department.department_name)}
                  >
                    ❯
                  </button>
                </div>
              </div>
            ))}
          </div>
        )}
      </section>
      <EventDetailsModal event={selectedEvent} onClose={closeEventModal} />
    </>
  );
};

const StudentDashboard = () => {
  const [posters] = useState([
    { id: 1, image: "/scroll/1.jpg" },
    { id: 2, image: "/scroll/2.jpg" },
    { id: 3, image: "/scroll/3.jpg" },
    { id: 4, image: "/scroll/4.jpg" },
  ]);

  const slides = [...posters, posters[0]];

  const [currentIndex, setCurrentIndex] = useState(0);
  const wrapperRef = useRef(null);
  const [showWelcome, setShowWelcome] = useState(true);
  const studentName = localStorage.getItem('studentName') || "";
  useEffect(() => {
    // This part now uses the 'studentName' state set above and just controls the timer
    const timer = setTimeout(() => {
      setShowWelcome(false);
    }, 3500);
    return () => clearTimeout(timer);
  }, []);

  useEffect(() => {
    if (slides.length <= 1) return;
    const interval = setInterval(() => {
      setCurrentIndex((prev) => prev + 1);
    }, 4000);
    return () => clearInterval(interval);
  }, [slides.length]);

  useEffect(() => {
    if (!wrapperRef.current) return;

    if (currentIndex === slides.length - 1) {
      setTimeout(() => {
        wrapperRef.current.style.transition = "none";
        setCurrentIndex(0);
      }, 900);
    } else {
      wrapperRef.current.style.transition =
        "transform 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94)";
    }
  }, [currentIndex, slides.length]);


  const goToNext = () => {
    setCurrentIndex((prev) => prev + 1);
  };

  const goToPrevious = () => {
    if (currentIndex === 0) {
      wrapperRef.current.style.transition = "none";
      setCurrentIndex(slides.length - 1);
      setTimeout(() => {
        wrapperRef.current.style.transition =
          "transform 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94)";
        setCurrentIndex(slides.length - 2);
      }, 10);
    } else {
      setCurrentIndex((prev) => prev - 1);
    }
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
      {showWelcome && studentName && <WelcomePopup name={studentName} />}
      <div className="carousel-container">
        <div
          className="carousel-wrapper"
          ref={wrapperRef}
          style={{
            width: `${slides.length * 100}%`,
            transform: `translateX(-${
              currentIndex * (100 / slides.length)
            }%)`,
          }}
        >
          {slides.map((poster, index) => (
            <div key={index} className="carousel-slide">
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
              className={`carousel-dot ${
                index ===
                (currentIndex === posters.length ? 0 : currentIndex)
                  ? "active"
                  : ""
              }`}
              onClick={() => goToSlide(index)}
            />
          ))}
        </div>
      </div>
      <main>
        <ManageEvents />
      </main>
    </>
  );
};

const WelcomePopup = ({ name }) => {
  return (
    <div className="welcome-popup-overlay">
      <div className="welcome-popup-content">
        <h1>Welcome, {name}!</h1>
      </div>
    </div>
  );
};

export default StudentDashboard;