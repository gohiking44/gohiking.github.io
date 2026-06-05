/**
 * Global Configuration Variables
 * This is a template - these values will be populated from config.php
 */
const GLOBAL_CONFIG = {
  // Will be populated dynamically
  googleMapsEmbedUrl: "",
  contactEmail: "",
  siteName: "",
  csrfToken: "",
}

/**
 * Initialize configuration from server
 */
async function initializeConfig() {
  try {
    const response = await fetch("get-config.php")
    if (response.ok) {
      const config = await response.json()
      Object.assign(GLOBAL_CONFIG, config)

      // Update contact info (email, phone)
      updateContactInfo()

      // Update Google Maps embed if present
      updateGoogleMapsEmbed()

      // Add CSRF token to forms
      addCSRFTokenToForms()
    }
  } catch (error) {
    console.error("Failed to load configuration:", error)
  }
}

/**
 * Update Google Maps embed URL dynamically
 */
function updateGoogleMapsEmbed() {
  const mapIframe = document.querySelector('iframe[src*="google.com/maps"]')
  if (mapIframe && GLOBAL_CONFIG.googleMapsEmbedUrl) {
    mapIframe.src = GLOBAL_CONFIG.googleMapsEmbedUrl
  }
}

/**
 * Add CSRF token to all forms
 */
function addCSRFTokenToForms() {
  if (!GLOBAL_CONFIG.csrfToken) return

  const forms = document.querySelectorAll("form")
  forms.forEach(form => {
    const csrfInput = document.createElement("input")
    csrfInput.type = "hidden"
    csrfInput.name = "csrf_token"
    csrfInput.value = GLOBAL_CONFIG.csrfToken
    form.appendChild(csrfInput)
  })
}

/**
 * Update contact email and phone throughout the page
 */
function updateContactInfo() {
  if (GLOBAL_CONFIG.contactEmail) {
    // Update top bar email
    const topbarEmail = document.querySelector("#topbar-email")
    if (topbarEmail) {
      topbarEmail.href = `mailto:${GLOBAL_CONFIG.contactEmail}`
      topbarEmail.textContent = GLOBAL_CONFIG.contactEmail
    }

    // Update contact section email display
    const contactEmails = document.querySelectorAll("[data-email]")
    contactEmails.forEach(el => {
      el.textContent = GLOBAL_CONFIG.contactEmail
    })
  }
}

/**
 * Handle contact form submission
 */
document.addEventListener("DOMContentLoaded", function () {
  const contactForm = document.querySelector(".php-email-form")
  if (contactForm) {
    contactForm.addEventListener("submit", handleFormSubmit)
  }

  // Initialize configuration on page load
  initializeConfig()
})

/**
 * Handle form submission with AJAX
 */
function handleFormSubmit(e) {
  e.preventDefault()

  const form = e.target
  const submitBtn = form.querySelector('button[type="submit"]')
  const loadingDiv = form.querySelector(".loading")
  const errorDiv = form.querySelector(".error-message")
  const successDiv = form.querySelector(".sent-message")

  // Reset messages
  errorDiv.innerHTML = ""
  successDiv.style.display = "none"
  loadingDiv.style.display = "block"

  // Disable submit button
  if (submitBtn) submitBtn.disabled = true

  // Prepare form data
  const formData = new FormData(form)

  // Send request
  fetch(form.action, {
    method: "POST",
    body: formData,
    headers: {
      Accept: "application/json",
    },
  })
    .then(response => {
      loadingDiv.style.display = "none"

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`)
      }
      return response.json()
    })
    .then(data => {
      if (data.success) {
        successDiv.style.display = "block"
        form.reset()

        // Re-add CSRF token after reset
        if (GLOBAL_CONFIG.csrfToken) {
          const csrfInput = form.querySelector('input[name="csrf_token"]')
          if (csrfInput) {
            csrfInput.value = GLOBAL_CONFIG.csrfToken
          }
        }
      } else {
        errorDiv.innerHTML =
          data.message || "Failed to send message. Please try again."
        errorDiv.style.display = "block"
      }
    })
    .catch(error => {
      loadingDiv.style.display = "none"
      errorDiv.innerHTML = "An error occurred. Please try again later."
      errorDiv.style.display = "block"
      console.error("Form submission error:", error)
    })
    .finally(() => {
      if (submitBtn) submitBtn.disabled = false
    })
}
