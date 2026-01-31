<?php
/**
 * Contact Page
 * Modern E-Commerce Platform
 */

require_once 'config/database.php';
require_once 'includes/functions.php';

$pageTitle = 'Contact Us';
$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $name = sanitize($_POST['name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $subject = sanitize($_POST['subject'] ?? '');
        $message = sanitize($_POST['message'] ?? '');
        
        // Validation
        if (empty($name) || empty($email) || empty($subject) || empty($message)) {
            $error = 'Please fill in all fields.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } else {
            // In production, you would send email or save to database
            // For now, we'll just show success message
            
            // Example: Save to database
            // $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, subject, message, created_at) VALUES (?, ?, ?, ?, NOW())");
            // $stmt->execute([$name, $email, $subject, $message]);
            
            // Example: Send email
            // mail(ADMIN_EMAIL, $subject, $message, "From: $email");
            
            $success = 'Thank you for your message! We\'ll get back to you soon.';
            
            // Clear form
            $name = $email = $subject = $message = '';
        }
    }
}

include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="relative bg-gradient-to-br from-primary-600 via-primary-700 to-primary-800 py-20 overflow-hidden">
    <div class="absolute inset-0 opacity-10">
        <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width=\"60\" height=\"60\" viewBox=\"0 0 60 60\" xmlns=\"http://www.w3.org/2000/svg\"%3E%3Cg fill=\"none\" fill-rule=\"evenodd\"%3E%3Cg fill=\"%23ffffff\" fill-opacity=\"0.4\"%3E%3Cpath d=\"M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')"></div>
    </div>
    <div class="container mx-auto px-4 relative">
        <div class="max-w-3xl mx-auto text-center text-white">
            <h1 class="text-4xl md:text-5xl font-bold mb-4" data-aos="fade-down">
                Get In Touch
            </h1>
            <p class="text-xl opacity-90" data-aos="fade-up" data-aos-delay="100">
                Have questions? We'd love to hear from you. Send us a message!
            </p>
        </div>
    </div>
</section>

<!-- Contact Section -->
<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="max-w-6xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Contact Info -->
                <div class="lg:col-span-1 space-y-6" data-aos="fade-right">
                    <!-- Card 1: Address -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm hover:shadow-lg transition-shadow">
                        <div class="w-14 h-14 bg-primary-100 rounded-xl flex items-center justify-center mb-4">
                            <i class="fas fa-map-marker-alt text-primary-500 text-xl"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Visit Us</h3>
                        <p class="text-gray-600">
                            123 Shopping Street<br>
                            New York, NY 10001<br>
                            United States
                        </p>
                    </div>
                    
                    <!-- Card 2: Phone -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm hover:shadow-lg transition-shadow">
                        <div class="w-14 h-14 bg-green-100 rounded-xl flex items-center justify-center mb-4">
                            <i class="fas fa-phone-alt text-green-500 text-xl"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Call Us</h3>
                        <p class="text-gray-600">
                            <a href="tel:+1234567890" class="hover:text-primary-500 transition-colors">
                                +1 (234) 567-890
                            </a>
                        </p>
                        <p class="text-sm text-gray-500 mt-1">Mon-Fri: 9AM - 6PM EST</p>
                    </div>
                    
                    <!-- Card 3: Email -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm hover:shadow-lg transition-shadow">
                        <div class="w-14 h-14 bg-blue-100 rounded-xl flex items-center justify-center mb-4">
                            <i class="fas fa-envelope text-blue-500 text-xl"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Email Us</h3>
                        <p class="text-gray-600">
                            <a href="mailto:support@shopmodern.com" class="hover:text-primary-500 transition-colors">
                                support@shopmodern.com
                            </a>
                        </p>
                        <p class="text-sm text-gray-500 mt-1">We reply within 24 hours</p>
                    </div>
                    
                    <!-- Social Links -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Follow Us</h3>
                        <div class="flex gap-3">
                            <a href="#" class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center text-gray-600 hover:bg-primary-500 hover:text-white transition-all">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="#" class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center text-gray-600 hover:bg-primary-500 hover:text-white transition-all">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="#" class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center text-gray-600 hover:bg-primary-500 hover:text-white transition-all">
                                <i class="fab fa-instagram"></i>
                            </a>
                            <a href="#" class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center text-gray-600 hover:bg-primary-500 hover:text-white transition-all">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Contact Form -->
                <div class="lg:col-span-2" data-aos="fade-left">
                    <div class="bg-white rounded-2xl p-8 shadow-sm">
                        <h2 class="text-2xl font-bold text-gray-800 mb-6">Send us a Message</h2>
                        
                        <?php if ($success): ?>
                            <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl flex items-center gap-3 text-green-700">
                                <i class="fas fa-check-circle text-xl"></i>
                                <span><?php echo $success; ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($error): ?>
                            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl flex items-center gap-3 text-red-700">
                                <i class="fas fa-exclamation-circle text-xl"></i>
                                <span><?php echo $error; ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" id="contact-form" class="space-y-6">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Name -->
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                        Your Name <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" id="name" name="name" required
                                           value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>"
                                           class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary-500 focus:ring-4 focus:ring-primary-100 transition-all"
                                           placeholder="John Doe">
                                </div>
                                
                                <!-- Email -->
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                        Email Address <span class="text-red-500">*</span>
                                    </label>
                                    <input type="email" id="email" name="email" required
                                           value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>"
                                           class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary-500 focus:ring-4 focus:ring-primary-100 transition-all"
                                           placeholder="john@example.com">
                                </div>
                            </div>
                            
                            <!-- Subject -->
                            <div>
                                <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">
                                    Subject <span class="text-red-500">*</span>
                                </label>
                                <select id="subject" name="subject" required
                                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary-500 focus:ring-4 focus:ring-primary-100 transition-all">
                                    <option value="">Select a subject</option>
                                    <option value="General Inquiry" <?php echo (isset($subject) && $subject === 'General Inquiry') ? 'selected' : ''; ?>>General Inquiry</option>
                                    <option value="Order Support" <?php echo (isset($subject) && $subject === 'Order Support') ? 'selected' : ''; ?>>Order Support</option>
                                    <option value="Returns & Refunds" <?php echo (isset($subject) && $subject === 'Returns & Refunds') ? 'selected' : ''; ?>>Returns & Refunds</option>
                                    <option value="Product Question" <?php echo (isset($subject) && $subject === 'Product Question') ? 'selected' : ''; ?>>Product Question</option>
                                    <option value="Partnership" <?php echo (isset($subject) && $subject === 'Partnership') ? 'selected' : ''; ?>>Partnership</option>
                                    <option value="Other" <?php echo (isset($subject) && $subject === 'Other') ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                            
                            <!-- Message -->
                            <div>
                                <label for="message" class="block text-sm font-medium text-gray-700 mb-2">
                                    Message <span class="text-red-500">*</span>
                                </label>
                                <textarea id="message" name="message" rows="6" required
                                          class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary-500 focus:ring-4 focus:ring-primary-100 transition-all resize-none"
                                          placeholder="Tell us how we can help..."><?php echo isset($message) ? htmlspecialchars($message) : ''; ?></textarea>
                            </div>
                            
                            <!-- Submit Button -->
                            <button type="submit" 
                                    class="w-full bg-gradient-to-r from-primary-500 to-primary-600 text-white py-4 rounded-xl font-semibold hover:shadow-lg hover:shadow-primary-500/30 transition-all flex items-center justify-center gap-2">
                                <i class="fas fa-paper-plane"></i>
                                Send Message
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Map Section -->
<section class="h-96">
    <iframe 
        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d193595.15830869428!2d-74.11976397304605!3d40.69766374874431!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c24fa5d33f083b%3A0xc80b8f06e177fe62!2sNew%20York%2C%20NY%2C%20USA!5e0!3m2!1sen!2s!4v1635959481234!5m2!1sen!2s"
        width="100%" 
        height="100%" 
        style="border:0;" 
        allowfullscreen="" 
        loading="lazy">
    </iframe>
</section>

<!-- FAQ Section -->
<section class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <div class="max-w-3xl mx-auto">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-800 mb-4">Frequently Asked Questions</h2>
                <p class="text-gray-600">Quick answers to common questions</p>
            </div>
            
            <div class="space-y-4">
                <!-- FAQ Item 1 -->
                <div class="border border-gray-200 rounded-xl overflow-hidden faq-item">
                    <button class="w-full px-6 py-4 text-left font-semibold text-gray-800 flex items-center justify-between hover:bg-gray-50 transition-colors faq-toggle">
                        <span>What are your shipping times?</span>
                        <i class="fas fa-chevron-down transition-transform"></i>
                    </button>
                    <div class="px-6 py-4 bg-gray-50 hidden faq-content">
                        <p class="text-gray-600">Standard shipping takes 5-7 business days. Express shipping is available for 2-3 business day delivery. International orders may take 10-14 business days depending on the destination.</p>
                    </div>
                </div>
                
                <!-- FAQ Item 2 -->
                <div class="border border-gray-200 rounded-xl overflow-hidden faq-item">
                    <button class="w-full px-6 py-4 text-left font-semibold text-gray-800 flex items-center justify-between hover:bg-gray-50 transition-colors faq-toggle">
                        <span>What is your return policy?</span>
                        <i class="fas fa-chevron-down transition-transform"></i>
                    </button>
                    <div class="px-6 py-4 bg-gray-50 hidden faq-content">
                        <p class="text-gray-600">We offer a 30-day return policy for all unused items in their original packaging. Simply contact our support team to initiate a return. Refunds are processed within 5-7 business days after we receive the item.</p>
                    </div>
                </div>
                
                <!-- FAQ Item 3 -->
                <div class="border border-gray-200 rounded-xl overflow-hidden faq-item">
                    <button class="w-full px-6 py-4 text-left font-semibold text-gray-800 flex items-center justify-between hover:bg-gray-50 transition-colors faq-toggle">
                        <span>How can I track my order?</span>
                        <i class="fas fa-chevron-down transition-transform"></i>
                    </button>
                    <div class="px-6 py-4 bg-gray-50 hidden faq-content">
                        <p class="text-gray-600">Once your order ships, you'll receive an email with tracking information. You can also log into your account and view your order status in the "My Orders" section.</p>
                    </div>
                </div>
                
                <!-- FAQ Item 4 -->
                <div class="border border-gray-200 rounded-xl overflow-hidden faq-item">
                    <button class="w-full px-6 py-4 text-left font-semibold text-gray-800 flex items-center justify-between hover:bg-gray-50 transition-colors faq-toggle">
                        <span>Do you offer international shipping?</span>
                        <i class="fas fa-chevron-down transition-transform"></i>
                    </button>
                    <div class="px-6 py-4 bg-gray-50 hidden faq-content">
                        <p class="text-gray-600">Yes! We ship to over 100 countries worldwide. International shipping rates and delivery times vary by location. You can see the shipping cost at checkout before completing your order.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// FAQ Toggle
document.querySelectorAll('.faq-toggle').forEach(button => {
    button.addEventListener('click', function() {
        const content = this.nextElementSibling;
        const icon = this.querySelector('i');
        
        // Close other FAQs
        document.querySelectorAll('.faq-content').forEach(c => {
            if (c !== content) {
                c.classList.add('hidden');
                c.previousElementSibling.querySelector('i').style.transform = 'rotate(0deg)';
            }
        });
        
        // Toggle current FAQ
        content.classList.toggle('hidden');
        icon.style.transform = content.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(180deg)';
    });
});

// Form validation with animation
document.getElementById('contact-form')?.addEventListener('submit', function(e) {
    const button = this.querySelector('button[type="submit"]');
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
    button.disabled = true;
});
</script>

<?php include 'includes/footer.php'; ?>
