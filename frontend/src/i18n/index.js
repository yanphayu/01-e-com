import { ref } from 'vue'

const STORAGE_KEY = 'locale'
const SUPPORTED = ['en', 'kh']
const FALLBACK = 'en'

const locale = ref(localStorage.getItem(STORAGE_KEY) || FALLBACK)

const messages = {
  en: {
    'nav.login': 'Login',
    'nav.register': 'Register',
    'nav.logout': 'Logout',

    'home.eyebrow': 'New season · 2026',
    'home.heroTitle1': 'Thoughtfully made,',
    'home.heroTitleAccent': 'effortlessly yours.',
    'home.heroSub':
      'Discover a curated collection designed around simplicity, quality, and a feeling of calm. Welcome to a fresher way to shop.',
    'home.startShopping': 'Start shopping',
    'home.signIn': 'Sign in',
    'home.shopByCategory': 'Shop by category',
    'home.shopByCategorySub': 'Clean essentials, picked with care.',
    'home.featuredPicks': 'Featured picks',
    'home.featuredSub': 'Fresh arrivals our community loves.',
    'home.ctaTitle': 'Ready for a calmer shopping experience?',
    'home.ctaSub': 'Create your free account and pick up right where you left off.',
    'home.ctaBtn': 'Create your account',
    'home.items': 'items',

    'auth.welcome': 'Welcome back',
    'auth.welcomeSub': 'Sign in to continue to TRINITY.',
    'auth.createAccount': 'Create your account',
    'auth.joinSub': 'Join TRINITY and start shopping.',
    'auth.email': 'Email',
    'auth.password': 'Password',
    'auth.confirmPassword': 'Confirm password',
    'auth.emailPlaceholder': 'you@example.com',
    'auth.passwordPlaceholder': 'At least 8 characters',
    'auth.confirmPlaceholder': 'Re-enter your password',
    'auth.login': 'Login',
    'auth.loggingIn': 'Logging in…',
    'auth.noAccount': 'No account?',
    'auth.createOne': 'Create one',
    'auth.alreadyAccount': 'Already have an account?',
    'auth.signInLink': 'Sign in',
    'auth.creatingAccount': 'Creating account…',
    'auth.passwordsMismatch': 'Passwords do not match',
    'auth.accountCreated': 'Account created! Please verify your email.',
    'auth.verifyTitle': 'Verify your email',
    'auth.verifySub': 'We sent a 6-digit code to',
    'auth.verifySubYour': 'your email',
    'auth.verified': 'Email verified — you’re all set!',
    'auth.expiredMsg': 'This code has expired. Please request a new one.',
    'auth.codeExpiresIn': 'Code expires in',
    'auth.verifyEmail': 'Verify email',
    'auth.verifying': 'Verifying…',
    'auth.verifiedBtn': 'Verified',
    'auth.didntGet': 'Didn’t get a code?',
    'auth.resend': 'Resend code',
    'auth.resendIn': 'Resend in',
    'auth.useDifferentEmail': 'Use a different email',

    'auth.profileTitle': 'Set up your profile',
    'auth.profileSub': 'Tell us a bit about you to finish your account.',
    'auth.firstName': 'First name',
    'auth.firstNamePlaceholder': 'Jane',
    'auth.lastName': 'Last name',
    'auth.lastNamePlaceholder': 'Doe',
    'auth.phone': 'Phone',
    'auth.phonePlaceholder': '+855 12 345 678',
    'auth.birthDate': 'Date of birth',
    'auth.birthDatePlaceholder': 'YYYY-MM-DD',
    'auth.address': 'Address',
    'auth.addressPlaceholder': 'Street, City, Country',
    'auth.getLocation': 'Get device location',
    'auth.gettingLocation': 'Getting location…',
    'auth.locationUnsupported': 'Geolocation is not supported on this device.',
    'auth.locationDenied': 'Location permission was denied.',
    'auth.avatar': 'Avatar',
    'auth.avatarPick': 'Choose a photo',
    'auth.avatarUploading': 'Uploading…',
    'auth.avatarTypeInvalid': 'Please choose an image file (JPG, PNG, GIF or WEBP).',
    'auth.socialTitle': 'Social profiles',
    'auth.facebook': 'Facebook',
    'auth.facebookPlaceholder': 'https://facebook.com/yourprofile',
    'auth.instagram': 'Instagram',
    'auth.instagramPlaceholder': 'https://instagram.com/yourprofile',
    'auth.twitter': 'Twitter / X',
    'auth.twitterPlaceholder': 'https://x.com/yourprofile',
    'auth.saveProfile': 'Save',
    'auth.savingProfile': 'Saving…',
    'auth.firstNameRequired': 'First name is required.',
    'auth.lastNameRequired': 'Last name is required.',
    'auth.phoneInvalid': 'Enter a valid phone number.',
    'auth.myProfile': 'My profile',
    'auth.editProfile': 'Edit profile',
    'auth.cancelEdit': 'Cancel',
    'auth.addNewAccount': 'Add new account',
    'auth.deleteProfile': 'Delete profile',
    'auth.deleteConfirmTitle': 'Delete your profile?',
    'auth.deleteConfirmBody': 'This will permanently delete your account and all your data. This action cannot be undone.',
    'auth.deleteAccount': 'Delete',
    'auth.deletingAccount': 'Deleting…',

    'footer.home': 'Home',
    'footer.getStarted': 'Get started',
    'footer.signIn': 'Sign in',
    'footer.rights': '© {year} TRINITY. All rights reserved.',

    'authLayout.tagline1': 'A calmer way',
    'authLayout.tagline2': 'to shop.',
    'authLayout.sub':
      'Join thousands discovering thoughtfully made products with a clean, fuss-free experience.',
    'authLayout.b1': 'Curated, quality-first collections',
    'authLayout.b2': 'Fast, secure checkout',
    'authLayout.b3': 'Free returns within 30 days',
    'authLayout.rights': '© {year} TRINITY. All rights reserved.',

    'locale.en': 'English',
    'locale.kh': 'ខ្មែរ',
    'locale.label': 'Language',
  },

  kh: {
    'nav.login': 'ចូល',
    'nav.register': 'ចំណុះឈ្មោះ',
    'nav.logout': 'ចាកចេញ',

    'home.eyebrow': 'រដូវកាលថ្មី · 2026',
    'home.heroTitle1': 'រចនាបែបគិតគូរ,',
    'home.heroTitleAccent': 'របស់អ្នកដោយគ្មានកង្វល់',
    'home.heroSub':
      'ស្វែងរកបណ្តុំផលិតផលដែលបានជ្រើសរើសយ៉ាងប្រុងប្រយ័ត្ន រចនាឡើងជុំវិញភាពងាយស្រួល គុណភាព និងអារម្មណ៍ស្ងប់ស្ងាត់។ ស្វាគមន៍ការទិញដែលស្រស់ស្រាយ។',
    'home.startShopping': 'ចាប់ផ្តើមទិញ',
    'home.signIn': 'ចូល',
    'home.shopByCategory': 'ទិញតាមប្រភេទ',
    'home.shopByCategorySub': 'គ្រឿងបរិក្ខារស្អាត ដែលជ្រើសរើសយ៉ាងប្រុងប្រយ័ត្ន',
    'home.featuredPicks': 'ជម្រើសពិសេស',
    'home.featuredSub': 'ការមកដល់ថ្មីដែលសហគមន៍របស់យើងចូលចិត្ត',
    'home.ctaTitle': 'រួចរាល់សម្រាប់បទពិសោធន៍ទិញដែលស្ងប់ស្ងាត់?',
    'home.ctaSub': 'បង្កើតគណនីឥតគិតថ្លៃ ហើយយកឡើងវិញពីកន្លែងដែលអ្នកបានទុកចោល',
    'home.ctaBtn': 'បង្កើតគណនីរបស់អ្នក',
    'home.items': 'មុខ',

    'auth.welcome': 'ស្វាគមន៍ការវិលត្រឡប់',
    'auth.welcomeSub': 'ចូលដើម្បីបន្តទៅ TRINITY',
    'auth.createAccount': 'បង្កើតគណនីរបស់អ្នក',
    'auth.joinSub': 'ចូលរួម TRINITY ហើយចាប់ផ្តើមទិញ',
    'auth.email': 'អ៊ីមែល',
    'auth.password': 'ពាក្យសម្ងាត់',
    'auth.confirmPassword': 'បញ្ជាក់ពាក្យសម្ងាត់',
    'auth.emailPlaceholder': 'you@example.com',
    'auth.passwordPlaceholder': 'យ៉ាងហោចណាស់ 8 តួអក្សរ',
    'auth.confirmPlaceholder': 'បញ្ចូលពាក្យសម្ងាត់ឡើងវិញ',
    'auth.login': 'ចូល',
    'auth.loggingIn': 'កំពុងចូល…',
    'auth.noAccount': 'គ្មានគណនី?',
    'auth.createOne': 'បង្កើតមួយ',
    'auth.alreadyAccount': 'មានគណនីរួចហើយ?',
    'auth.signInLink': 'ចូល',
    'auth.creatingAccount': 'កំពុងបង្កើតគណនី…',
    'auth.passwordsMismatch': 'ពាក្យសម្ងាត់មិនត្រូវគ្នា',
    'auth.accountCreated': 'បានបង្កើតគណនី! សូមផ្ទៀងផ្ទាត់អ៊ីមែលរបស់អ្នក។',
    'auth.verifyTitle': 'ផ្ទៀងផ្ទាត់អ៊ីមែលរបស់អ្នក',
    'auth.verifySub': 'យើងបានផ្ញើលេខកូដ 6 ខ្ទង់ទៅ',
    'auth.verifySubYour': 'អ៊ីមែលរបស់អ្នក',
    'auth.verified': 'ផ្ទៀងផ្ទាត់អ៊ីមែលរួចរាល់ — អ្នករួចរាល់!',
    'auth.expiredMsg': 'លេខកូដនេះបានផុតកំណត់។ សូមស្នើសុំថ្មី។',
    'auth.codeExpiresIn': 'លេខកូដផុតកំណត់ក្នុង',
    'auth.verifyEmail': 'ផ្ទៀងផ្ទាត់អ៊ីមែល',
    'auth.verifying': 'កំពុងផ្ទៀងផ្ទាត់…',
    'auth.verifiedBtn': 'បានផ្ទៀងផ្ទាត់',
    'auth.didntGet': 'មិនទទួលបានលេខកូដ?',
    'auth.resend': 'ផ្ញើលេខកូដឡើងវិញ',
    'auth.resendIn': 'ផ្ញើឡើងវិញក្នុង',
    'auth.useDifferentEmail': 'ប្រើអ៊ីមែលផ្សេង',

    'auth.profileTitle': 'រៀបចំប្រវត្តិរបស់អ្នក',
    'auth.profileSub': 'ប្រាប់ពីខ្លួនអ្នកបន្តិចដើម្បីបញ្ចប់គណនី។',
    'auth.firstName': 'ឈ្មោះខាងដើម',
    'auth.firstNamePlaceholder': 'Jane',
    'auth.lastName': 'ឈ្មោះគ្រួសារ',
    'auth.lastNamePlaceholder': 'Doe',
    'auth.phone': 'ទូរស័ព្ទ',
    'auth.phonePlaceholder': '+855 12 345 678',
    'auth.birthDate': 'ថ្ងៃខែឆ្នាំកំណើត',
    'auth.birthDatePlaceholder': 'YYYY-MM-DD',
    'auth.address': 'អាសយដ្ឋាន',
    'auth.addressPlaceholder': 'ផ្លូវ, ទីក្រុង, ប្រទេស',
    'auth.getLocation': 'យកទីតាំងរបស់ឧបករណ៍',
    'auth.gettingLocation': 'កំពុងយកទីតាំង…',
    'auth.locationUnsupported': 'ឧបករណ៍នេះមិនគាំទ្រការកំណត់ទីតាំងទេ។',
    'auth.locationDenied': 'ការអនុញ្ញាតទីតាំងត្រូវបានបដិសេធ។',
    'auth.avatar': 'រូបតំណាង',
    'auth.avatarPick': 'ជ្រើសរើសរូបថត',
    'auth.avatarUploading': 'កំពុងផ្ទុកឡើង…',
    'auth.avatarTypeInvalid': 'សូមជ្រើសរើសឯកសាររូបភាព (JPG, PNG, GIF ឬ WEBP)។',
    'auth.socialTitle': 'ប្រវត្តិបណ្ដាញសង្គម',
    'auth.facebook': 'Facebook',
    'auth.facebookPlaceholder': 'https://facebook.com/yourprofile',
    'auth.instagram': 'Instagram',
    'auth.instagramPlaceholder': 'https://instagram.com/yourprofile',
    'auth.twitter': 'Twitter / X',
    'auth.twitterPlaceholder': 'https://x.com/yourprofile',
    'auth.saveProfile': 'រក្សាទុក',
    'auth.savingProfile': 'កំពុងរក្សាទុក…',
    'auth.firstNameRequired': 'ឈ្មោះខាងដើមត្រូវបានទាមទារ។',
    'auth.lastNameRequired': 'ឈ្មោះគ្រួសារត្រូវបានទាមទារ។',
    'auth.phoneInvalid': 'បញ្ចូលលេខទូរស័ព្ទឱ្យបានត្រឹមត្រូវ។',
    'auth.myProfile': 'ប្រវត្តិរបស់ខ្ញុំ',
    'auth.editProfile': 'កែប្រវត្តិ',
    'auth.cancelEdit': 'បោះបង់',
    'auth.addNewAccount': 'បន្ថែមគណនីថ្មី',
    'auth.deleteProfile': 'លុបប្រវត្តិ',
    'auth.deleteConfirmTitle': 'លុបប្រវត្តិរបស់អ្នក?',
    'auth.deleteConfirmBody': 'វានឹងលុបគណនី និងទិន្នន័យទាំងអស់របស់អ្នកជាអចិន្ត្រៃយ៍។ សកម្មភាពនេះមិនអាចត្រឡប់វិញបានទេ។',
    'auth.deleteAccount': 'លុប',
    'auth.deletingAccount': 'កំពុងលុប…',

    'footer.home': 'ទំព័រដើម',
    'footer.getStarted': 'ចាប់ផ្តើម',
    'footer.signIn': 'ចូល',
    'footer.rights': '© {year} TRINITY. រក្សាសិទ្ធិគ្រប់យ៉ាង។',

    'authLayout.tagline1': 'វិធីដែល',
    'authLayout.tagline2': 'ស្ងប់ស្ងាត់ក្នុងការទិញ',
    'authLayout.sub':
      'ចូលរួមបុគ្គលរាប់ពាន់នាក់ដែលកំពុងរកឃើញផលិតផលដែលផលិតឡើងយ៉ាងប្រុងប្រយ័ត្ន ជាមួយបទពិសោធន៍ដែលស្អាត និងគ្មានភាពរញ៉័រ។',
    'authLayout.b1': 'បណ្តុំផលិតផលដែលជ្រើសរើស គុណភាពខ្ពស់',
    'authLayout.b2': 'ការទូទាត់រហ័ស និងមានសុវត្ថិភាព',
    'authLayout.b3': 'សិទ្ធិប្តូរឥតគិតថ្លៃក្នុងរយៈពេល 30 ថ្ងៃ',
    'authLayout.rights': '© {year} TRINITY. រក្សាសិទ្ធិគ្រប់យ៉ាង។',

    'locale.en': 'English',
    'locale.kh': 'ខ្មែរ',
    'locale.label': 'ភាសា',
  },
}

export function t(key, params = {}) {
  const dict = messages[locale.value] || messages[FALLBACK]
  let str = dict[key] ?? messages[FALLBACK][key] ?? key
  for (const [k, v] of Object.entries(params)) {
    str = str.replace(new RegExp(`\\{${k}\\}`, 'g'), v)
  }
  return str
}

export function setLocale(lang) {
  if (!SUPPORTED.includes(lang)) return
  locale.value = lang
  localStorage.setItem(STORAGE_KEY, lang)
  document.documentElement.setAttribute('lang', lang)
}

export function getLocale() {
  return locale.value
}

export { SUPPORTED, FALLBACK }
export const localeRef = locale
export default { t, setLocale, getLocale, SUPPORTED, FALLBACK, localeRef }
