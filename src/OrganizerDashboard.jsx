import { useState, useEffect, useRef } from "react";
import "./OrganizerDashboard.css";

const NavLink = ({ href, children }) => (
  <a href={href} className="nav-link" tabIndex="0">
    {children}
    <span className="nav-link-underline"></span>
  </a>
);

const Navbar = () => {
  const [isScrolled, setIsScrolled] = useState(false);
  const [studentName] = useState(localStorage.getItem('studentName') || '');
  const [isDropdownOpen, setIsDropdownOpen] = useState(false);

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
            ORGANIZER DASHBOARD
          </a>
          <div className="nav-links">
            <NavLink href="/organizer-dashboard">Home</NavLink>
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

// Modal component to display event details (MODIFIED)
const EventDetailsModal = ({ event, onClose, onDeleteSuccess }) => {
  if (!event) return null;

  // Function to handle the actual deletion via API
  const handleDelete = async () => {
    // Confirmation before deletion
    if (!window.confirm(`Are you sure you want to delete the event: ${event.eventName} (ID: ${event.id})?`)) {
      return;
    }

    try {
      // FIX: Use the query parameter format for the DELETE request to ensure server receives the ID
      const response = await fetch(`http://localhost/smart/eventdash.php?id=${event.id}`, {
        method: 'DELETE',
      });

      const result = await response.json();

      if (result.success) {
        alert(result.message || 'Event deleted successfully!');
        onClose(); // Close the modal
        
        // FIX: Add a small delay (100ms) before calling the refresh function
        setTimeout(() => {
          onDeleteSuccess(); // Call the function passed from ManageEvents to refresh the list
        }, 100); 

      } else {
        alert(result.error || result.message || 'Failed to delete event.');
        console.error('Delete error:', result);
      }
    } catch (e) {
      alert('Network error: Could not connect to the server to delete the event.');
      console.error('Network delete error:', e);
    }
  };

  return (
    <div className="modal-overlay" onClick={onClose}>
      <div className="modal-content" onClick={(e) => e.stopPropagation()}>
        <div className="modal-controls"> 
          <button className="modal-delete-button" onClick={handleDelete} title="Delete Event">
            <i className='bx bx-trash'></i> {/* Delete Icon */}
          </button>
          <button className="modal-close-button" onClick={onClose}>
            &times;
          </button>
        </div>
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

const CreateEventModal = ({ isOpen, onClose, refreshEvents, prefilledDepartment }) => { 
    // State must be at the top to avoid React Hook errors
    const [eventName, setEventName] = useState('');
    const [department, setDepartment] = useState(''); 
    const [posterFile, setPosterFile] = useState(null); 
    const [errorMessage, setErrorMessage] = useState('');
    const [successMessage, setSuccessMessage] = useState('');
    const [eventDate, setEventDate] = useState('');
    const [eventTime, setEventTime] = useState('');
    const [venue, setVenue] = useState(''); 
    const [eventLink, setEventLink] = useState('');
    const [firstPrize, setFirstPrize] = useState(0); 
    const [secondPrize, setSecondPrize] = useState(0); 
    const [thirdPrize, setThirdPrize] = useState(0); 

    // FIX 1: Reset the form state when the modal opens or the prefilledDepartment changes
    useEffect(() => {
        // Only set department if a prefilled value is provided
        setDepartment(prefilledDepartment || '');

        // Reset all other fields to ensure a clean form on every open
        setEventName('');
        // setDepartment(prefilledDepartment || ''); // Already handled above
        setPosterFile(null);
        setErrorMessage('');
        setSuccessMessage('');
        setEventDate('');
        setEventTime('');
        setVenue('');
        setEventLink('');
        setFirstPrize(0);
        setSecondPrize(0);
        setThirdPrize(0);
        
    }, [prefilledDepartment, isOpen]); // Rerun effect when department or open state changes


    if (!isOpen) return null;

    const handleSubmit = async (e) => {
        e.preventDefault();
        setErrorMessage('');
        setSuccessMessage('');

        if (!posterFile) {
             setErrorMessage("Poster file is required.");
             return;
        }

        // Use FormData to correctly send file and text data to PHP
        const formData = new FormData();
        formData.append('event_name', eventName);
        formData.append('department', department);
        formData.append('date', eventDate);
        formData.append('time', eventTime);
        formData.append('venue', venue);
        formData.append('poster', posterFile); 
        formData.append('event_links', eventLink);
        formData.append('first_prizes', firstPrize);
        formData.append('second_prizes', secondPrize);
        formData.append('third_prizes', thirdPrize);

        try {
            const response = await fetch('http://localhost/smart/eventdash.php', {
                method: 'POST',
                body: formData,
            });

            const result = await response.json();

            if (result.success) {
                setSuccessMessage(result.message);
                if (refreshEvents) refreshEvents(); 
                setTimeout(onClose, 2000); 
            } else {
                setErrorMessage(result.error || result.message || 'Failed to create event. Check console for details.');
            }
        } catch (error) {
            console.error('Network error or server issue:', error);
            setErrorMessage('Network error or internal server issue. Check your console.');
        }
    };

    return (
        <div className="modal-overlay" onClick={onClose}>
            <div className="create-event-modal-content" onClick={(e) => e.stopPropagation()}>
                <button className="modal-close-button" onClick={onClose}>
                    &times;
                </button>
                <h2 className="modal-title-form">Create New Event</h2>
                <p className="modal-subtitle">Fill in the details to create a new event</p>
                
                {errorMessage && <div className="error-message-form">{errorMessage}</div>}
                {successMessage && <div className="success-message">{successMessage}</div>}

                <form onSubmit={handleSubmit} className="create-event-form">
                    
                    <div className="form-row">
                        <div className="form-group">
                            <label htmlFor="eventName">Event Name *</label>
                            <input type="text" id="eventName" name="event_name" required placeholder="Enter event name" value={eventName} onChange={(e) => setEventName(e.target.value)} />
                        </div>
                        <div className="form-group">
                            <label htmlFor="department">Department *</label>
                            <input 
                                type="text" 
                                id="department" 
                                name="department" 
                                required 
                                placeholder="Enter department" 
                                value={department} 
                                onChange={(e) => setDepartment(e.target.value)} 
                            />
                        </div>
                    </div>

                    <div className="form-row">
                        <div className="form-group">
                            <label htmlFor="eventDate">Event Date *</label>
                            <input type="date" id="eventDate" name="date" value={eventDate} onChange={(e) => setEventDate(e.target.value)} required />
                        </div>
                        <div className="form-group">
                            <label htmlFor="eventTime">Event Time *</label>
                            <input type="time" id="eventTime" name="time" value={eventTime} onChange={(e) => setEventTime(e.target.value)} required />
                        </div>
                    </div>
                    
                    <div className="form-row">
                        <div className="form-group">
                            <label htmlFor="venue">Venue *</label>
                            <input type="text" id="venue" name="venue" required placeholder="Enter venue" value={venue} onChange={(e) => setVenue(e.target.value)} />
                        </div>
                        <div className="form-group">
                            <label htmlFor="posterName">Poster * (Image File)</label>
                            <input type="file" id="posterName" name="poster" accept="image/*" onChange={(e) => setPosterFile(e.target.files[0])} required /> 
                        </div>
                    </div>
                    
                    <div className="form-group full-width">
                        <label htmlFor="eventLink">Event Links (optional)</label>
                        <input type="url" id="eventLink" name="event_links" placeholder="Enter event links (optional)" value={eventLink} onChange={(e) => setEventLink(e.target.value)} />
                    </div>

                    <div className="form-row prize-input">
                        <div className="form-group prize-input">
                            <label htmlFor="1stPrize">1st Prizes</label>
                            <input type="number" id="1stPrize" name="first_prizes" value={firstPrize} onChange={(e) => setFirstPrize(e.target.value)} min="0" />
                        </div>
                        <div className="form-group prize-input">
                            <label htmlFor="2ndPrize">2nd Prizes</label>
                            <input type="number" id="2ndPrize" name="second_prizes" value={secondPrize} onChange={(e) => setSecondPrize(e.target.value)} min="0" />
                        </div>
                        <div className="form-group prize-input">
                            <label htmlFor="3rdPrize">3rd Prizes</label>
                            <input type="number" id="3rdPrize" name="third_prizes" value={thirdPrize} onChange={(e) => setThirdPrize(e.target.value)} min="0" />
                        </div>
                    </div>
                    
                    <div className="form-actions">
                        <button type="button" className="btn-secondary" onClick={onClose}>Cancel</button>
                        <button type="submit" className="btn-primary">Create Event</button>
                    </div>
                </form>
            </div>
        </div>
    );
};


const ManageEvents = () => {
  const [departments, setDepartments] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [selectedEvent, setSelectedEvent] = useState(null);
  const [isCreateModalOpen, setIsCreateModalOpen] = useState(false);
  const [prefilledDepartment, setPrefilledDepartment] = useState(''); // New state to hold the clicked department name
  const scrollContainerRefs = useRef({});

  const fetchAllEvents = async () => {
    setLoading(true);
    try {
      const response = await fetch("http://localhost/smart/deptposters.php");
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      const data = await response.json();
      setDepartments(data.departments);
      setError(null);
    } catch (e) {
      setError(
        "Failed to load department posters. Please check the backend connection."
      );
      console.error("Fetching error: ", e);
    } finally {
      setLoading(false);
    }
  };


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
  
  // Function to close the Create Modal and clear the department state
  const closeCreateModal = () => {
      setIsCreateModalOpen(false);
      setPrefilledDepartment(''); // Clear the prefilled name when closing
  }


  useEffect(() => {
    fetchAllEvents();
  }, []); // Initial load

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
                <h2 className="department-title">
                  {department.department_name}
                  {/* PLUS ICON: Triggers the new modal and sets the department name */}
                  <i 
                    className="bx bx-plus create-icon" 
                    onClick={(e) => {
                        e.stopPropagation(); 
                        setPrefilledDepartment(department.department_name); // Set the name here
                        setIsCreateModalOpen(true);
                    }}
                  ></i>
                </h2>
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
                            loading="lazy"
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
      {/* Existing Event Details Modal */}
      <EventDetailsModal event={selectedEvent} onClose={closeEventModal} onDeleteSuccess={fetchAllEvents} />
      
      {/* NEW: Create Event Modal - Passes the prefilled name */}
      <CreateEventModal 
        isOpen={isCreateModalOpen} 
        onClose={closeCreateModal} // Use dedicated close function
        refreshEvents={fetchAllEvents}
        prefilledDepartment={prefilledDepartment} // Pass the name
      />
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

const OrganizerDashboard = () => {
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

export default OrganizerDashboard;