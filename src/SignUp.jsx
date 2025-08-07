import React, { useState } from 'react';

const SignUp = () => {
  const [formData, setFormData] = useState({
    username: '',
    rollNo: '',
    department: '',
    yearOfGraduation: '',
    role: '',
    password: '',
    confirmPassword: ''
  });

  const [errors, setErrors] = useState({});
  const [isLoading, setIsLoading] = useState(false);

  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: value
    }));

    // Clear error on input
    if (errors[name]) {
      setErrors(prev => ({
        ...prev,
        [name]: ''
      }));
    }
  };

  const validateForm = () => {
    const newErrors = {};

    if (formData.password !== formData.confirmPassword) {
      newErrors.confirmPassword = 'Passwords do not match';
    }

    if (formData.password.length < 6) {
      newErrors.password = 'Password must be at least 6 characters long';
    }

    if (!formData.username.trim()) newErrors.username = 'Username is required';
    if (!formData.rollNo.trim()) newErrors.rollNo = 'Roll number is required';
    if (!formData.department.trim()) newErrors.department = 'Department is required';
    if (!formData.yearOfGraduation.trim()) newErrors.yearOfGraduation = 'Year of graduation is required';
    if (!formData.role) newErrors.role = 'Role is required';

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = async (e) => {
    e.preventDefault();

    if (!validateForm()) return;

    setIsLoading(true);

    try {
      const response = await fetch('http://localhost/smart/signup.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(formData)
      });

      const result = await response.json();

      if (result.status === "success") {
        alert('Account created successfully!');
        setFormData({
          username: '',
          rollNo: '',
          department: '',
          yearOfGraduation: '',
          role: '',
          password: '',
          confirmPassword: ''
        });
      } else {
        alert('Error: ' + result.message);
      }

    } catch (error) {
      console.error('Error submitting form:', error);
      alert('Submission failed. Please check your server.');
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className="wrapper">
      <form onSubmit={handleSubmit}>
        <h1>SIGN UP</h1>

        <div className="input-box">
          <input
            type="text"
            name="username"
            value={formData.username}
            onChange={handleInputChange}
            placeholder="USERNAME"
            className={errors.username ? 'error' : ''}
            required
          />
          <i className="bx bxs-user"></i>
          {errors.username && <span className="error-message">{errors.username}</span>}
        </div>

        <div className="input-box">
          <input
            type="text"
            name="rollNo"
            value={formData.rollNo}
            onChange={handleInputChange}
            placeholder="ROLL NO"
            className={errors.rollNo ? 'error' : ''}
            required
          />
          <i className="bx bxs-id-card"></i>
          {errors.rollNo && <span className="error-message">{errors.rollNo}</span>}
        </div>

        <div className="input-box">
          <input
            type="text"
            name="department"
            value={formData.department}
            onChange={handleInputChange}
            placeholder="DEPARTMENT"
            className={errors.department ? 'error' : ''}
            required
          />
          <i className="bx bxs-building"></i>
          {errors.department && <span className="error-message">{errors.department}</span>}
        </div>

        <div className="input-box">
          <input
            type="number"
            name="yearOfGraduation"
            value={formData.yearOfGraduation}
            onChange={handleInputChange}
            placeholder="YEAR OF GRADUATION"
            min="2020"
            max="2030"
            className={errors.yearOfGraduation ? 'error' : ''}
            required
          />
          <i className="bx bxs-calendar"></i>
          {errors.yearOfGraduation && <span className="error-message">{errors.yearOfGraduation}</span>}
        </div>

        <div className="input-box">
          <select
            name="role"
            value={formData.role}
            onChange={handleInputChange}
            className={errors.role ? 'error' : ''}
            required
            style={{
              color: formData.role === '' ? 'rgba(255, 255, 255, 1.0)' : '#fff'
            }}
          >
            <option value="" disabled style={{ color: 'rgba(255, 255, 255, 0.7)' }}>SELECT ROLE</option>
            <option value="student">Student</option>
            <option value="admin">Admin</option>
            <option value="organizer">Organizer</option>
          </select>
          <i className="bx bxs-user-circle"></i>
          {errors.role && <span className="error-message">{errors.role}</span>}
        </div>

        <div className="input-box">
          <input
            type="password"
            name="password"
            value={formData.password}
            onChange={handleInputChange}
            placeholder="PASSWORD"
            className={errors.password ? 'error' : ''}
            required
          />
          <i className="bx bxs-lock"></i>
          {errors.password && <span className="error-message">{errors.password}</span>}
        </div>

        <div className="input-box">
          <input
            type="password"
            name="confirmPassword"
            value={formData.confirmPassword}
            onChange={handleInputChange}
            placeholder="CONFIRM PASSWORD"
            className={errors.confirmPassword ? 'error' : ''}
            required
          />
          <i className="bx bxs-lock"></i>
          {errors.confirmPassword && <span className="error-message">{errors.confirmPassword}</span>}
        </div>

        <button className="btn" type="submit" disabled={isLoading}>
          {isLoading ? 'Creating Account...' : 'Sign Up'}
        </button>

        <div className="register">
          <p>Already Have an Account? <a href="/login" style={{ color: '#fff', textDecoration: 'none' }}>Login</a></p>
        </div>
      </form>
    </div>
  );
};

export default SignUp;
