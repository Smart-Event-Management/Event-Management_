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
    
    // Clear error when user starts typing
    if (errors[name]) {
      setErrors(prev => ({
        ...prev,
        [name]: ''
      }));
    }
  };

  const validateForm = () => {
    const newErrors = {};

    // Check if passwords match
    if (formData.password !== formData.confirmPassword) {
      newErrors.confirmPassword = 'Passwords do not match';
    }

    // Check password strength
    if (formData.password.length < 6) {
      newErrors.password = 'Password must be at least 6 characters long';
    }

    // Check required fields
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
    
    if (!validateForm()) {
      return;
    }

    setIsLoading(true);
    
    try {
      // Simulate API call
      await new Promise(resolve => setTimeout(resolve, 2000));
      
      console.log('Form submitted successfully:', formData);
      alert('Account created successfully!');
      
      // Reset form
      setFormData({
        username: '',
        rollNo: '',
        department: '',
        yearOfGraduation: '',
        role: '',
        password: '',
        confirmPassword: ''
      });
      
    } catch (error) {
      console.error('Error submitting form:', error);
      alert('Error creating account. Please try again.');
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
            required 
            placeholder="USERNAME"
            className={errors.username ? 'error' : ''}
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
            required 
            placeholder="ROLL NO"
            className={errors.rollNo ? 'error' : ''}
          />
          <i className="bx bxs-id-card"></i>
          {errors.rollNo && <span className="error-message">{errors.rollNo}</span>}
        </div>


        <div className="input-box">
          <input 
            type="number" 
            name="yearOfGraduation"
            value={formData.yearOfGraduation}
            onChange={handleInputChange}
            required 
            placeholder="YEAR OF GRADUATION"
            min="2020"
            max="2030"
            className={errors.yearOfGraduation ? 'error' : ''}
          />
          <i className="bx bxs-calendar"></i>
          {errors.yearOfGraduation && <span className="error-message">{errors.yearOfGraduation}</span>}
        </div>

        <div className="input-box">
          <select 
            name="role"
            value={formData.role}
            onChange={handleInputChange}
            required
            className={errors.role ? 'error' : ''}
            style={{
              color: formData.role === '' ? 'rgba(255, 255, 255, 1.0)' : '#fff'
            }}
          >
            <option value="" disabled style={{color: 'rgba(255, 255, 255, 0.7)'}}>SELECT ROLE</option>
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
            required 
            placeholder="PASSWORD"
            className={errors.password ? 'error' : ''}
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
            required 
            placeholder="CONFIRM PASSWORD"
            className={errors.confirmPassword ? 'error' : ''}
          />
          <i className="bx bxs-lock"></i>
          {errors.confirmPassword && <span className="error-message">{errors.confirmPassword}</span>}
        </div>

        <button 
          className="btn" 
          type="submit" 
          disabled={isLoading}
        >
          {isLoading ? 'Creating Account...' : 'Sign Up'}
        </button>

        <div className="register">
          <p>Already Have an Account? <a href="/login" style={{color: '#fff', textDecoration: 'none'}}>Login</a></p>
        </div>
      </form>
    </div>
  );
};

export default SignUp;