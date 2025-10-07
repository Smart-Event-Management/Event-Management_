import { useState, useEffect, useRef } from "react";
import "./OrganizerDashboard.css";
import Lottie from "lottie-react";
import welcomeAnimation from "./Welcome.json"; // Make sure this path is correct
// Utility function to decode HTML entities for display
function decodeHtml(html) {
  const txt = document.createElement("textarea");
  txt.innerHTML = html;
  return txt.value;
}

// Utility function to convert Time from DB (e.g., "10:00 AM" or "14:00:00") to HTML input format (HH:MM - 24 hour)
function formatTimeForInput(timeStr) {
  if (!timeStr || timeStr === "00:00:00") return "";

  // Normalize to a string and handle potential trailing spaces/characters
  timeStr = String(timeStr).trim();

  let match = timeStr.match(/(\d{1,2}):(\d{2})/);
  if (match) {
    let hours = parseInt(match[1], 10);
    const minutes = match[2];

    // Convert 12-hour AM/PM to 24-hour format if needed
    if (timeStr.toLowerCase().includes("pm") && hours < 12) {
      hours += 12;
    } else if (timeStr.toLowerCase().includes("am") && hours === 12) {
      hours = 0; // Midnight 12 AM
    }

    // Return 24-hour HH:MM format (required by <input type="time">)
    const paddedHours = String(hours).padStart(2, "0");
    return `${paddedHours}:${minutes}`;
  }
  // Fallback if no clean match, try to return first 5 characters (HH:MM)
  return timeStr.substring(0, 5);
}

// Utility function to convert Date from DB (e.g., "30.06.2025" or "2025-06-30") to HTML input format (YYYY-MM-DD)
function formatDateForInput(dateStr) {
  if (!dateStr || dateStr.includes("Febr")) return "";

  let parts = String(dateStr).substring(0, 10).split(/[-/.]/);

  if (parts.length === 3) {
    // YYYY-MM-DD (SQL Standard)
    if (parts[0].length === 4) {
      return `${parts[0]}-${parts[1].padStart(2, "0")}-${parts[2].padStart(
        2,
        "0"
      )}`;
    }
    // DD-MM-YYYY or MM-DD-YYYY (Assume DD-MM-YYYY if YYYY is last)
    if (parts[2].length === 4) {
      return `${parts[2]}-${parts[1].padStart(2, "0")}-${parts[0].padStart(
        2,
        "0"
      )}`;
    }
  }
  return "";
}

const NavLink = ({ href, children }) => (
  <a href={href} className="nav-link" tabIndex="0">
    {children}
    <span className="nav-link-underline"></span>
  </a>
);

const Navbar = () => {
  const [isScrolled, setIsScrolled] = useState(false);
  const [studentName] = useState(localStorage.getItem("studentName") || "");
  const [isDropdownOpen, setIsDropdownOpen] = useState(false);

  useEffect(() => {
    const handleScroll = () => setIsScrolled(window.scrollY > 10);
    window.addEventListener("scroll", handleScroll);
    return () => window.removeEventListener("scroll", handleScroll);
  }, []);

  const handleSignOut = () => {
    // Use clear() for a full sign-out
    localStorage.clear();
    window.location.href = "/login";
  };

  const toggleDropdown = () => {
    setIsDropdownOpen((prev) => !prev);
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
            <NavLink href="https://rvrjcce.ac.in/xfeedback.php">
              Contact
            </NavLink>

            {studentName ? (
              // Profile Dropdown Button
              <div className="profile-dropdown-container">
                <button onClick={toggleDropdown} className="profile-button">
                  <span className="profile-name">{studentName}</span>
                  <i
                    className={`bx bx-chevron-down dropdown-arrow ${
                      isDropdownOpen ? "open" : ""
                    }`}
                  ></i>
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
const EventDetailsModal = ({
  event,
  onClose,
  onDeleteSuccess,
  onEditStart,
}) => {
  if (!event) return null;

  // Function to handle the actual deletion via API
  const handleDelete = async () => {
    if (
      !window.confirm(
        `Are you sure you want to delete the event: ${event.eventName} (ID: ${event.id})?`
      )
    ) {
      return;
    }

    try {
      // Use the query parameter format for the DELETE request to ensure server receives the ID
      const response = await fetch(
        `http://localhost/smart/eventdash.php?id=${event.id}`,
        {
          method: "DELETE",
        }
      );

      const result = await response.json();

      if (result.success) {
        window.alert(result.message || "Event deleted successfully!");
        onClose(); // Close the modal

        // Add a small delay (100ms) before calling the refresh function to prevent network conflict
        setTimeout(() => {
          onDeleteSuccess();
        }, 100);
      } else {
        window.alert(
          result.error || result.message || "Failed to delete event."
        );
        console.error("Delete error:", result);
      }
    } catch (e) {
      window.alert(
        "Network error: Could not connect to the server to delete the event."
      );
      console.error("Network delete error:", e);
    }
  };

  const handleEditClick = () => {
    onClose(); // Close the current detail modal
    onEditStart(event); // Pass the current event data back to ManageEvents
  };

  return (
    <div className="modal-overlay" onClick={onClose}>
      <div className="modal-content" onClick={(e) => e.stopPropagation()}>
        <button className="modal-close-button" onClick={onClose}>
          &times;
        </button>
        <div className="modal-controls">
          <button
            className="icon-control-button modal-edit-button"
            onClick={handleEditClick}
            title="Edit Event Details"
          >
            <i className="bx bx-edit"></i>
          </button>

          <button
            className="icon-control-button modal-delete-button"
            onClick={handleDelete}
            title="Delete Event"
          >
            <i className="bx bx-trash"></i>
          </button>
        </div>
        <div className="modal-header">
          {/* Using http://localhost/smart/public/posters/ for universal server path */}
          <img
            src={`http://localhost/posters/${event.poster}`}
            alt={event.eventName}
            className="modal-poster"
          />
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
              <strong>Event Link:</strong>{" "}
              <a href={event.link} target="_blank" rel="noopener noreferrer">
                {event.link}
              </a>
            </p>
          )}
          {event.First_prizes && (
            <p>
              <strong>1st Prize:</strong> {event.First_prizes}
            </p>
          )}
          {event.Second_prizes && (
            <p>
              <strong>2nd Prize:</strong> {event.Second_prizes}
            </p>
          )}
          {event.Third_prizes && (
            <p>
              <strong>3rd Prize:</strong> {event.Third_prizes}
            </p>
          )}
        </div>
      </div>
    </div>
  );
};

const EventFormModal = ({
  isOpen,
  onClose,
  refreshEvents,
  initialEventData,
  prefilledDepartment,
}) => {
  // State must be at the top to avoid React Hook errors
  const isEditMode = !!initialEventData; // True if editing an existing event

  // 1. Initial State Assignment (Reads existing data for edit, or blank for create)
  const [eventName, setEventName] = useState("");
  const [department, setDepartment] = useState("");
  const [posterFile, setPosterFile] = useState(null);
  const [errorMessage, setErrorMessage] = useState("");
  const [successMessage, setSuccessMessage] = useState("");
  const [eventDate, setEventDate] = useState("");
  const [eventTime, setEventTime] = useState("");
  const [venue, setVenue] = useState("");
  const [eventLink, setEventLink] = useState("");
  const [firstPrize, setFirstPrize] = useState(0);
  const [secondPrize, setSecondPrize] = useState(0);
  const [thirdPrize, setThirdPrize] = useState(0);

  // Use effect to populate the form fields when in edit mode or when department changes (autofill)
  useEffect(() => {
    // 1. Reset all fields first to ensure no residual data
    setEventName("");
    setDepartment("");
    setEventDate("");
    setEventTime("");
    setVenue("");
    setEventLink("");
    setFirstPrize(0);
    setSecondPrize(0);
    setThirdPrize(0);
    setPosterFile(null);
    setErrorMessage("");
    setSuccessMessage("");

    if (isEditMode && initialEventData) {
      // Using the expected snake_case properties, ensuring default '' if null/undefined
      setEventName(initialEventData.event_name ?? "");
      setDepartment(initialEventData.department ?? "");
      setEventDate(formatDateForInput(initialEventData.date));
      setEventTime(formatTimeForInput(initialEventData.time));
      setVenue(initialEventData.venue ?? "");
      setEventLink(initialEventData.event_links ?? "");
      setFirstPrize(Number(initialEventData.first_prizes) || 0);
      setSecondPrize(Number(initialEventData.second_prizes) || 0);
      setThirdPrize(Number(initialEventData.third_prizes) || 0);
    } else if (prefilledDepartment) {
      // Autofill in Create Mode
      setDepartment(prefilledDepartment);
    }
  }, [initialEventData, prefilledDepartment, isOpen, isEditMode]);

  if (!isOpen) return null;

  const handleSubmit = async (e) => {
    e.preventDefault();
    setErrorMessage("");
    setSuccessMessage("");

    // --- FIX APPLIED HERE: Enforce poster requirement ---
    const hasExistingPoster =
      isEditMode &&
      initialEventData.poster_name &&
      initialEventData.poster_name.trim() !== "";

    if (!posterFile && !hasExistingPoster) {
      setErrorMessage(
        "Poster file is required for this event. Please select a new file or ensure the existing poster name is present."
      );
      return;
    }
    // --- END FIX ---

    const formData = new FormData();
    formData.append("event_name", eventName);
    formData.append("department", department);
    formData.append("date", eventDate);
    formData.append("time", eventTime); // Now directly uses the 24h format from <input type="time">
    formData.append("venue", venue);
    formData.append("event_links", eventLink);
    formData.append("first_prizes", firstPrize);
    formData.append("second_prizes", secondPrize);
    formData.append("third_prizes", thirdPrize);

    // Handle file upload or preservation
    if (posterFile) {
      formData.append("poster", posterFile); // Send the new file
    } else if (isEditMode) {
      // CRITICAL: Send the old filename to preserve the existing poster if no new file is selected
      formData.append("poster_name", initialEventData.poster_name);
    }

    // Determine API URL and Method
    const url = isEditMode
      ? `http://localhost/smart/eventdash.php?id=${initialEventData.id}`
      : "http://localhost/smart/eventdash.php";
    const method = "POST"; // PHP handles file uploads via POST, which we use for both Create and Update

    try {
      const response = await fetch(url, {
        method: method,
        body: formData,
      });

      const result = await response.json();

      if (result.success) {
        setSuccessMessage(result.message);
        if (refreshEvents) refreshEvents();
        setTimeout(onClose, 2000);
      } else {
        setErrorMessage(
          result.error ||
            result.message ||
            "Failed to save event. Check console for details."
        );
      }
    } catch (error) {
      console.error("Network error or server issue:", error);
      setErrorMessage(
        "Network error or internal server issue. Check your console."
      );
    }
  };

  return (
    <div className="modal-overlay" onClick={onClose}>
      <div
        className="create-event-modal-content"
        onClick={(e) => e.stopPropagation()}
      >
        <button className="modal-close-button" onClick={onClose}>
          &times;
        </button>
        <h2 className="modal-title-form">
          {isEditMode ? "Edit Existing Event" : "Create New Event"}
        </h2>
        <p className="modal-subtitle">
          Fill in the details to create a new event
        </p>

        {errorMessage && (
          <div className="error-message-form">{errorMessage}</div>
        )}
        {successMessage && (
          <div className="success-message">{successMessage}</div>
        )}

        <form onSubmit={handleSubmit} className="create-event-form">
          <div className="form-row">
            <div className="form-group">
              <label htmlFor="eventName">Event Name *</label>
              <input
                type="text"
                id="eventName"
                name="event_name"
                required
                placeholder="Enter event name"
                value={eventName}
                onChange={(e) => setEventName(e.target.value)}
              />
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
              <input
                type="date"
                id="eventDate"
                name="date"
                value={eventDate}
                onChange={(e) => setEventDate(e.target.value)}
                required
              />
            </div>
            <div className="form-group">
              <label htmlFor="eventTime">Event Time *</label>
              <input
                type="time" // Reverted to standard HTML 24-hour time input
                id="eventTime"
                name="time"
                value={eventTime}
                onChange={(e) => setEventTime(e.target.value)}
                required
              />
            </div>
          </div>

          <div className="form-row">
            <div className="form-group">
              <label htmlFor="venue">Venue *</label>
              <input
                type="text"
                id="venue"
                name="venue"
                required
                placeholder="Enter venue"
                value={venue}
                onChange={(e) => setVenue(e.target.value)}
              />
            </div>
            <div className="form-group">
              <label htmlFor="posterName">Poster * (Image File)</label>
              <input
                type="file"
                id="posterName"
                name="poster"
                accept="image/*"
                onChange={(e) => setPosterFile(e.target.files[0])}
                required={!isEditMode} // FIXED: Only required for new events
              />
              {isEditMode && (
                <small className="current-poster-note">
                  {initialEventData.poster_name}
                </small>
              )}
            </div>
          </div>

          <div className="form-group full-width">
            <label htmlFor="eventLink">Event Links (optional)</label>
            <input
              type="url"
              id="eventLink"
              name="event_links"
              placeholder="Enter event links (optional)"
              value={eventLink}
              onChange={(e) => setEventLink(e.target.value)}
            />
          </div>

          <div className="form-row prize-input">
            <div className="form-group prize-input">
              <label htmlFor="1stPrize">1st Prizes</label>
              <input
                type="number"
                id="1stPrize"
                name="first_prizes"
                value={firstPrize}
                onChange={(e) => setFirstPrize(e.target.value)}
                min="0"
              />
            </div>
            <div className="form-group prize-input">
              <label htmlFor="2ndPrize">2nd Prizes</label>
              <input
                type="number"
                id="2ndPrize"
                name="second_prizes"
                value={secondPrize}
                onChange={(e) => setSecondPrize(e.target.value)}
                min="0"
              />
            </div>
            <div className="form-group prize-input">
              <label htmlFor="3rdPrize">3rd Prizes</label>
              <input
                type="number"
                id="3rdPrize"
                name="third_prizes"
                value={thirdPrize}
                onChange={(e) => setThirdPrize(e.target.value)}
                min="0"
              />
            </div>
          </div>

          <div className="form-actions">
            <button type="button" className="btn-secondary" onClick={onClose}>
              Cancel
            </button>
            <button type="submit" className="btn-primary">
              {isEditMode ? "Save Changes" : "Create Event"}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};
const LazyLoadWrapper = ({ children, height = 300 }) => {
  // Now accepts a height prop
  const [isVisible, setIsVisible] = useState(false);
  const ref = useRef(null);

  useEffect(() => {
    const currentRef = ref.current;

    const observer = new IntersectionObserver(
      ([entry]) => {
        if (entry.isIntersecting) {
          setIsVisible(true);
          if (currentRef) {
            observer.unobserve(currentRef);
          }
        }
      },
      {
        // INCREASED: Start loading when the item is 600px away from the viewport.
        // This gives the browser plenty of time. You can adjust this value.
        rootMargin: "850px",
      }
    );

    if (currentRef) {
      observer.observe(currentRef);
    }

    return () => {
      if (currentRef) {
        observer.unobserve(currentRef);
      }
    };
  }, []);

  // Uses the height prop for the placeholder to prevent page jumping
  return (
    <div ref={ref} style={{ minHeight: `${height}px` }}>
      {isVisible ? children : null}
    </div>
  );
};
const ManageEvents = () => {
  const [departments, setDepartments] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [selectedEvent, setSelectedEvent] = useState(null);
  const [eventToEdit, setEventToEdit] = useState(null); // State to hold event data for editing
  const [isCreateModalOpen, setIsCreateModalOpen] = useState(false);
  const [prefilledDepartment, setPrefilledDepartment] = useState(""); // State to hold the clicked department name
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
      const response = await fetch(
        `http://localhost/smart/poster-button-click.php?id=${eventId}`
      );
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      const data = await response.json();
      if (data.success) {
        setSelectedEvent(data.data);
        // Clear edit state when opening details modal
        setEventToEdit(null);
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

  // Function to transition from details modal to edit form
  const startEditEvent = (eventData) => {
    // FIX APPLIED HERE: Map the EventDetailsModal's keys (which are likely using PascalCase/camelCase)
    // back to the snake_case keys that the EventFormModal's useEffect is expecting.
    const mappedEventData = {
      ...eventData,
      event_name: eventData.eventName, // Mapping 'eventName' to 'event_name'
      event_links: eventData.link, // Mapping 'link' to 'event_links'
      first_prizes: eventData.First_prizes, // Mapping 'First_prizes' to 'first_prizes'
      second_prizes: eventData.Second_prizes, // Mapping 'Second_prizes' to 'second_prizes'
      third_prizes: eventData.Third_prizes, // Mapping 'Third_prizes' to 'third_prizes'
    };

    setEventToEdit(mappedEventData); // Set the mapped data to be edited
    setSelectedEvent(null); // Close the detail modal
    setIsCreateModalOpen(true); // Open the form modal
  };

  // Function to close the Create/Edit Form Modal and clear all related states
  const closeFormModal = () => {
    setIsCreateModalOpen(false);
    setPrefilledDepartment(""); // Clear autofill state
    setEventToEdit(null); // Clear edit state
  };

  useEffect(() => {
    fetchAllEvents();
  }, []); // Initial load

  return (
    <>
      <section className="event-management-container">
        <div className="event-tabs">
          <div className="event-tab active-tab">ACTIVE EVENTS</div>
        </div>

        {loading && (
          <div className="loading-state">Loading department posters...</div>
        )}

        {error && <div className="error-state">{error}</div>}

        {!loading && !error && (
          <div className="department-posters-container">
            {departments.map((department) => (
              <LazyLoadWrapper key={department.department_name} height={500}>
                <div className="department-section">
                  <h2 className="department-title">
                    {decodeHtml(department.department_name)}
                    {/* PLUS ICON: Triggers the new modal and sets the department name */}
                    <i
                      className="bx bx-plus create-icon"
                      onClick={(e) => {
                        e.stopPropagation();
                        setPrefilledDepartment(
                          decodeHtml(department.department_name)
                        ); // Set the decoded name here
                        setEventToEdit(null); // Ensure creation mode is active
                        setIsCreateModalOpen(true);
                      }}
                    ></i>
                  </h2>
                  <div className="poster-scroll-wrapper">
                    <button
                      className="scroll-button left"
                      onClick={() =>
                        handleScroll(-300, department.department_name)
                      }
                    >
                      ❮
                    </button>
                    <div
                      className="poster-scroll-container"
                      ref={(el) =>
                        (scrollContainerRefs.current[
                          department.department_name
                        ] = el)
                      }
                    >
                      {department.events.length > 0 ? (
                        department.events.map((event) => (
                          <div
                            key={event.id}
                            className="poster-item"
                            onClick={() => openEventModal(event.id)}
                          >
                            <img
                              // Use the correct base URL for image display
                              src={`http://localhost/posters/${event.poster_name}`}
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
                      onClick={() =>
                        handleScroll(300, department.department_name)
                      }
                    >
                      ❯
                    </button>
                  </div>
                </div>
              </LazyLoadWrapper>
            ))}
          </div>
        )}
      </section>
      {/* Existing Event Details Modal (Now includes Edit handler) */}
      <EventDetailsModal
        event={selectedEvent}
        onClose={closeEventModal}
        onDeleteSuccess={fetchAllEvents}
        onEditStart={startEditEvent} // Passed new edit function
      />

      {/* NEW: Event Form Modal (Used for both Create and Edit) */}
      <EventFormModal
        isOpen={isCreateModalOpen || !!eventToEdit} // Open if creating OR editing
        onClose={closeFormModal} // Use dedicated close function
        refreshEvents={fetchAllEvents}
        prefilledDepartment={prefilledDepartment} // Pass the name
        initialEventData={eventToEdit} // Pass event data here for editing
      />
    </>
  );
};

const WelcomePopup = () => {
  return (
    <div className="welcome-popup-overlay">
      <div className="welcome-popup-content">
        <Lottie 
          animationData={welcomeAnimation} 
          loop={true} 
          className="popup-lottie-animation"
        />
        {/* The progress bar remains to show how long until it closes */}
        <div className="popup-progress-bar"></div>
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
  const studentName = localStorage.getItem("studentName") || "";
 
  useEffect(() => {
    const timer = setTimeout(() => {
      setShowWelcome(false);
    }, 5000);
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

  // ADDED: This useEffect hook tracks user activity
  useEffect(() => {
    const markUserAsActive = async () => {
      const userRole = localStorage.getItem("userRole");
      const userId = localStorage.getItem("userId");

      if (userRole && userId) {
        try {
          await fetch("http://localhost/smart/update_activity.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ role: userRole, id: userId }),
          });
        } catch (error) {
          console.error("Could not update user activity:", error);
        }
      }
    };

    markUserAsActive();
  }, []);

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
            transform: `translateX(-${(currentIndex * 100) / slides.length}%)`,
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
                index === (currentIndex === posters.length ? 0 : currentIndex)
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
