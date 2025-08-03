import React from "react";
import { Formik, Form, Field } from "formik";

const validate = (values) => {
  const errors = {};
  if (!values.firstName) {
    errors.firstName = "First Name cannot be empty";
  } else if (!/^[A-Za-z]+$/i.test(values.firstName)) {
    errors.firstName = "First Name should contain only letters";
  }

  if (!values.lastName) {
    errors.lastName = "Last Name cannot be empty";
  } else if (values.lastName.length > 20) {
    errors.lastName = "Must be 20 characters or less";
  }

  if (!values.email) {
    errors.email = "Email is required";
  } else if (!/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i.test(values.email)) {
    errors.email = "Invalid email address";
  }

  if (!values.password) {
    errors.password = "Password is required";
  } else if (values.password.length < 8) {
    errors.password = "Password must not be less than 8 characters";
  }

  return errors;
};

function FormSection() {
  return (
    <section className="section-container">
      <button className="trial-btn text-white">
        <span className="text-bold">Try it free 7 days</span> then $20/mo.
        thereafter
      </button>

      <Formik
        initialValues={{
          firstName: "",
          lastName: "",
          email: "",
          password: "",
        }}
        validate={validate}
        onSubmit={(values, { setSubmitting }) => {
          setTimeout(() => {
            alert(JSON.stringify(values, null, 2));
            setSubmitting(false);
          }, 400);
        }}
      >
        {({ isSubmitting, errors, touched }) => (
          <Form className="form-container">
            <Field
              type="text"
              placeholder="First Name"
              name="firstName"
              id="firstName"
              className={
                errors.firstName && touched.firstName ? "input-error" : ""
              }
            />
            <Field
              type="text"
              placeholder="Last Name"
              name="lastName"
              id="lastName"
              className={
                errors.lastName && touched.lastName ? "input-error" : ""
              }
            />
            <Field
              type="email"
              placeholder="Email Address"
              name="email"
              id="email"
              className={errors.email && touched.email ? "input-error" : ""}
            />
            <Field
              type="password"
              placeholder="Password"
              name="password"
              id="password"
              className={
                errors.password && touched.password ? "input-error" : ""
              }
            />
            <button
              type="submit"
              className="submit-btn text-white cursor-pointer"
              disabled={isSubmitting}
            >
              CLAIM YOUR FREE TRIAL
            </button>
            {Object.keys(errors).length > 0 && (
              <div className="error-summary">
                <p>Please fix the following errors:</p>
                <ul>
                  {Object.keys(errors).map((key) => (
                    <li key={key}>{errors[key]}</li>
                  ))}
                </ul>
              </div>
            )}
          </Form>
        )}
      </Formik>
      <p className="terms-text">
        By clicking the button, you are agreeing to our&nbsp;
        <a href="nothing" className="terms-link">
          Terms and Services
        </a>
      </p>
    </section>
  );
}

export default FormSection;