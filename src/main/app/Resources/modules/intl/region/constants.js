import {trans} from '#/main/app/intl/translation'

const LANGS = {
  'ab': {
    'name': 'Abkhaz',
    'nativeName': 'аҧсуа'
  },
  'af': {
    'name': 'Afrikaans',
    'nativeName': 'Afrikaans'
  },
  'sq': {
    'name': 'Albanian',
    'nativeName': 'Shqip'
  },
  'am': {
    'name': 'Amharic',
    'nativeName': 'አማርኛ'
  },
  'ar': {
    'name': 'Arabic',
    'nativeName': 'العربية'
  },
  'an': {
    'name': 'Aragonese',
    'nativeName': 'Aragonés'
  },
  'hy': {
    'name': 'Armenian',
    'nativeName': 'Հայերեն'
  },
  'as': {
    'name': 'Assamese',
    'nativeName': 'অসমীয়া'
  },
  'ay': {
    'name': 'Aymara',
    'nativeName': 'aymar aru'
  },
  'az': {
    'name': 'Azerbaijani',
    'nativeName': 'azərbaycan dili'
  },
  'ba': {
    'name': 'Bashkir',
    'nativeName': 'башҡорт теле'
  },
  'eu': {
    'name': 'Basque',
    'nativeName': 'euskara, euskera'
  },
  'be': {
    'name': 'Belarusian',
    'nativeName': 'Беларуская'
  },
  'bn': {
    'name': 'Bengali',
    'nativeName': 'বাংলা'
  },
  'bh': {
    'name': 'Bihari',
    'nativeName': 'भोजपुरी'
  },
  'bi': {
    'name': 'Bislama',
    'nativeName': 'Bislama'
  },
  'br': {
    'name': 'Breton',
    'nativeName': 'brezhoneg'
  },
  'bg': {
    'name': 'Bulgarian',
    'nativeName': 'български език'
  },
  'my': {
    'name': 'Burmese',
    'nativeName': 'ဗမာစာ'
  },
  'ca': {
    'name': 'Catalan; Valencian',
    'nativeName': 'Català'
  },
  'zh': {
    'name': 'Chinese',
    'nativeName': '中文 (Zhōngwén), 汉语, 漢語'
  },
  'hr': {
    'name': 'Croatian',
    'nativeName': 'hrvatski'
  },
  'cs': {
    'name': 'Czech',
    'nativeName': 'česky, čeština'
  },
  'da': {
    'name': 'Danish',
    'nativeName': 'dansk'
  },
  'nl': {
    'name': 'Dutch',
    'nativeName': 'Nederlands, Vlaams'
  },
  'en': {
    'name': 'English',
    'nativeName': 'English'
  },
  'eo': {
    'name': 'Esperanto',
    'nativeName': 'Esperanto'
  },
  'et': {
    'name': 'Estonian',
    'nativeName': 'eesti, eesti keel'
  },
  'fi': {
    'name': 'Finnish',
    'nativeName': 'suomi, suomen kieli'
  },
  'fr': {
    'name': 'French',
    'nativeName': 'Français'
  },
  'gl': {
    'name': 'Galician',
    'nativeName': 'Galego'
  },
  'ka': {
    'name': 'Georgian',
    'nativeName': 'ქართული'
  },
  'de': {
    'name': 'German',
    'nativeName': 'Deutsch'
  },
  'el': {
    'name': 'Greek',
    'nativeName': 'Ελληνικά'
  },
  'gn': {
    'name': 'Guaraní',
    'nativeName': 'Avañeẽ'
  },
  'gu': {
    'name': 'Gujarati',
    'nativeName': 'ગુજરાતી'
  },
  'ht': {
    'name': 'Haitian; Haitian Creole',
    'nativeName': 'Kreyòl ayisyen'
  },
  'ha': {
    'name': 'Hausa',
    'nativeName': 'Hausa, هَوُسَ'
  },
  'he': {
    'name': 'Hebrew',
    'nativeName': 'עברית'
  },
  'hi': {
    'name': 'Hindi',
    'nativeName': 'हिन्दी, हिंदी'
  },
  'hu': {
    'name': 'Hungarian',
    'nativeName': 'Magyar'
  },
  'ia': {
    'name': 'Interlingua',
    'nativeName': 'Interlingua'
  },
  'id': {
    'name': 'Indonesian',
    'nativeName': 'Bahasa Indonesia'
  },
  'ie': {
    'name': 'Interlingue',
    'nativeName': 'Originally called Occidental; then Interlingue after WWII'
  },
  'ga': {
    'name': 'Irish',
    'nativeName': 'Gaeilge'
  },
  'io': {
    'name': 'Ido',
    'nativeName': 'Ido'
  },
  'is': {
    'name': 'Icelandic',
    'nativeName': 'Íslenska'
  },
  'it': {
    'name': 'Italian',
    'nativeName': 'Italiano'
  },
  'iu': {
    'name': 'Inuktitut',
    'nativeName': 'ᐃᓄᒃᑎᑐᑦ'
  },
  'ja': {
    'name': 'Japanese',
    'nativeName': '日本語 (にほんご／にっぽんご)'
  },
  'jv': {
    'name': 'Javanese',
    'nativeName': 'basa Jawa'
  },
  'kl': {
    'name': 'Kalaallisut, Greenlandic',
    'nativeName': 'kalaallisut, kalaallit oqaasii'
  },
  'kn': {
    'name': 'Kannada',
    'nativeName': 'ಕನ್ನಡ'
  },
  'ks': {
    'name': 'Kashmiri',
    'nativeName': 'कश्मीरी, كشميري‎'
  },
  'kk': {
    'name': 'Kazakh',
    'nativeName': 'Қазақ тілі'
  },
  'rw': {
    'name': 'Kinyarwanda',
    'nativeName': 'Ikinyarwanda'
  },
  'ky': {
    'name': 'Kirghiz, Kyrgyz',
    'nativeName': 'кыргыз тили'
  },
  'kg': {
    'name': 'Kongo',
    'nativeName': 'KiKongo'
  },
  'ko': {
    'name': 'Korean',
    'nativeName': '한국어 (韓國語), 조선말 (朝鮮語)'
  },
  'ku': {
    'name': 'Kurdish',
    'nativeName': 'Kurdî, كوردی‎'
  },
  'la': {
    'name': 'Latin',
    'nativeName': 'latine, lingua latina'
  },
  'li': {
    'name': 'Limburgish, Limburgan, Limburger',
    'nativeName': 'Limburgs'
  },
  'ln': {
    'name': 'Lingala',
    'nativeName': 'Lingála'
  },
  'lo': {
    'name': 'Lao',
    'nativeName': 'ພາສາລາວ'
  },
  'lt': {
    'name': 'Lithuanian',
    'nativeName': 'lietuvių kalba'
  },
  'lv': {
    'name': 'Latvian',
    'nativeName': 'latviešu valoda'
  },
  'gv': {
    'name': 'Manx',
    'nativeName': 'Gaelg, Gailck'
  },
  'mk': {
    'name': 'Macedonian',
    'nativeName': 'македонски јазик'
  },
  'mg': {
    'name': 'Malagasy',
    'nativeName': 'Malagasy fiteny'
  },
  'ms': {
    'name': 'Malay',
    'nativeName': 'bahasa Melayu, بهاس ملايو‎'
  },
  'ml': {
    'name': 'Malayalam',
    'nativeName': 'മലയാളം'
  },
  'mt': {
    'name': 'Maltese',
    'nativeName': 'Malti'
  },
  'mi': {
    'name': 'Māori',
    'nativeName': 'te reo Māori'
  },
  'mr': {
    'name': 'Marathi (Marāṭhī)',
    'nativeName': 'मराठी'
  },
  'mn': {
    'name': 'Mongolian',
    'nativeName': 'монгол'
  },
  'na': {
    'name': 'Nauru',
    'nativeName': 'Ekakairũ Naoero'
  },
  'ne': {
    'name': 'Nepali',
    'nativeName': 'नेपाली'
  },
  'no': {
    'name': 'Norwegian',
    'nativeName': 'Norsk'
  },
  'oc': {
    'name': 'Occitan',
    'nativeName': 'Occitan'
  },
  'om': {
    'name': 'Oromo',
    'nativeName': 'Afaan Oromoo'
  },
  'or': {
    'name': 'Oriya',
    'nativeName': 'ଓଡ଼ିଆ'
  },
  'pa': {
    'name': 'Panjabi, Punjabi',
    'nativeName': 'ਪੰਜਾਬੀ, پنجابی‎'
  },
  'pl': {
    'name': 'Polish',
    'nativeName': 'polski'
  },
  'ps': {
    'name': 'Pashto, Pushto',
    'nativeName': 'پښتو'
  },
  'pt': {
    'name': 'Portuguese',
    'nativeName': 'Português'
  },
  'qu': {
    'name': 'Quechua',
    'nativeName': 'Runa Simi, Kichwa'
  },
  'rn': {
    'name': 'Kirundi',
    'nativeName': 'kiRundi'
  },
  'ro': {
    'name': 'Romanian, Moldavian, Moldovan',
    'nativeName': 'română'
  },
  'ru': {
    'name': 'Russian',
    'nativeName': 'русский язык'
  },
  'sa': {
    'name': 'Sanskrit (Saṁskṛta)',
    'nativeName': 'संस्कृतम्'
  },
  'sd': {
    'name': 'Sindhi',
    'nativeName': 'सिन्धी, سنڌي، سندھی‎'
  },
  'sm': {
    'name': 'Samoan',
    'nativeName': 'gagana faa Samoa'
  },
  'sr': {
    'name': 'Serbian',
    'nativeName': 'српски језик'
  },
  'gd': {
    'name': 'Scottish Gaelic; Gaelic',
    'nativeName': 'Gàidhlig'
  },
  'sn': {
    'name': 'Shona',
    'nativeName': 'chiShona'
  },
  'si': {
    'name': 'Sinhala, Sinhalese',
    'nativeName': 'සිංහල'
  },
  'sk': {
    'name': 'Slovak',
    'nativeName': 'slovenčina'
  },
  'so': {
    'name': 'Somali',
    'nativeName': 'Soomaaliga, af Soomaali'
  },
  'st': {
    'name': 'Southern Sotho',
    'nativeName': 'Sesotho'
  },
  'es': {
    'name': 'Spanish; Castilian',
    'nativeName': 'español, castellano'
  },
  'su': {
    'name': 'Sundanese',
    'nativeName': 'Basa Sunda'
  },
  'sw': {
    'name': 'Swahili',
    'nativeName': 'Kiswahili'
  },
  'ss': {
    'name': 'Swati',
    'nativeName': 'Siswati'
  },
  'sv': {
    'name': 'Swedish',
    'nativeName': 'svenska'
  },
  'ta': {
    'name': 'Tamil',
    'nativeName': 'தமிழ்'
  },
  'te': {
    'name': 'Telugu',
    'nativeName': 'తెలుగు'
  },
  'tg': {
    'name': 'Tajik',
    'nativeName': 'тоҷикӣ, toğikī, تاجیکی‎'
  },
  'th': {
    'name': 'Thai',
    'nativeName': 'ไทย'
  },
  'ti': {
    'name': 'Tigrinya',
    'nativeName': 'ትግርኛ'
  },
  'bo': {
    'name': 'Tibetan Standard, Tibetan, Central',
    'nativeName': 'བོད་ཡིག'
  },
  'tk': {
    'name': 'Turkmen',
    'nativeName': 'Türkmen, Түркмен'
  },
  'tl': {
    'name': 'Tagalog',
    'nativeName': 'Wikang Tagalog, ᜏᜒᜃᜅ᜔ ᜆᜄᜎᜓᜄ᜔'
  },
  'tn': {
    'name': 'Tswana',
    'nativeName': 'Setswana'
  },
  'to': {
    'name': 'Tonga (Tonga Islands)',
    'nativeName': 'faka Tonga'
  },
  'tr': {
    'name': 'Turkish',
    'nativeName': 'Türkçe'
  },
  'ts': {
    'name': 'Tsonga',
    'nativeName': 'Xitsonga'
  },
  'tt': {
    'name': 'Tatar',
    'nativeName': 'татарча, tatarça, تاتارچا‎'
  },
  'tw': {
    'name': 'Twi',
    'nativeName': 'Twi'
  },
  'ug': {
    'name': 'Uighur, Uyghur',
    'nativeName': 'Uyƣurqə, ئۇيغۇرچە‎'
  },
  'uk': {
    'name': 'Ukrainian',
    'nativeName': 'українська'
  },
  'ur': {
    'name': 'Urdu',
    'nativeName': 'اردو'
  },
  'uz': {
    'name': 'Uzbek',
    'nativeName': 'zbek, Ўзбек, أۇزبېك‎'
  },
  'vi': {
    'name': 'Vietnamese',
    'nativeName': 'Tiếng Việt'
  },
  'vo': {
    'name': 'Volapük',
    'nativeName': 'Volapük'
  },
  'wa': {
    'name': 'Walloon',
    'nativeName': 'Walon'
  },
  'cy': {
    'name': 'Welsh',
    'nativeName': 'Cymraeg'
  },
  'wo': {
    'name': 'Wolof',
    'nativeName': 'Wollof'
  },
  'fy': {
    'name': 'Western Frisian',
    'nativeName': 'Frysk'
  },
  'xh': {
    'name': 'Xhosa',
    'nativeName': 'isiXhosa'
  },
  'yi': {
    'name': 'Yiddish',
    'nativeName': 'ייִדיש'
  },
  'yo': {
    'name': 'Yoruba',
    'nativeName': 'Yorùbá'
  },
  'zu': {
    'name': 'Zulu',
    'nativeName': 'isiZulu'
  }
}

/**
 * The list of world regions codes.
 *
 * NB.
 * The list comes from the symfony Intl component (translations too)
 * but there is no way to access it from client without additional AJAX
 * so we have just c/p it for now
 *
 * It must be kept synced with `regions` translation domain
 *
 * @type {Array}
 */
const REGION_CODES = [
  'AC',
  'AD',
  'AE',
  'AF',
  'AG',
  'AI',
  'AL',
  'AM',
  'AO',
  'AQ',
  'AR',
  'AS',
  'AT',
  'AU',
  'AW',
  'AX',
  'AZ',
  'BA',
  'BB',
  'BD',
  'BE',
  'BF',
  'BG',
  'BH',
  'BI',
  'BJ',
  'BL',
  'BM',
  'BN',
  'BO',
  'BQ',
  'BR',
  'BS',
  'BT',
  'BW',
  'BY',
  'BZ',
  'CA',
  'CC',
  'CD',
  'CF',
  'CG',
  'CH',
  'CI',
  'CK',
  'CL',
  'CM',
  'CN',
  'CO',
  'CR',
  'CU',
  'CV',
  'CW',
  'CX',
  'CY',
  'CZ',
  'DE',
  'DG',
  'DJ',
  'DK',
  'DM',
  'DO',
  'DZ',
  'EA',
  'EC',
  'EE',
  'EG',
  'EH',
  'ER',
  'ES',
  'ET',
  'FI',
  'FJ',
  'FK',
  'FM',
  'FO',
  'FR',
  'GA',
  'GB',
  'GD',
  'GE',
  'GF',
  'GG',
  'GH',
  'GI',
  'GL',
  'GM',
  'GN',
  'GP',
  'GQ',
  'GR',
  'GS',
  'GT',
  'GU',
  'GW',
  'GY',
  'HK',
  'HN',
  'HR',
  'HT',
  'HU',
  'IC',
  'ID',
  'IE',
  'IL',
  'IM',
  'IN',
  'IO',
  'IQ',
  'IR',
  'IS',
  'IT',
  'JE',
  'JM',
  'JO',
  'JP',
  'KE',
  'KG',
  'KH',
  'KI',
  'KM',
  'KN',
  'KP',
  'KR',
  'KW',
  'KY',
  'KZ',
  'LA',
  'LB',
  'LC',
  'LI',
  'LK',
  'LR',
  'LS',
  'LT',
  'LU',
  'LV',
  'LY',
  'MA',
  'MC',
  'MD',
  'ME',
  'MF',
  'MG',
  'MH',
  'MK',
  'ML',
  'MM',
  'MN',
  'MO',
  'MP',
  'MQ',
  'MR',
  'MS',
  'MT',
  'MU',
  'MV',
  'MW',
  'MX',
  'MY',
  'MZ',
  'NA',
  'NC',
  'NE',
  'NF',
  'NG',
  'NI',
  'NL',
  'NO',
  'NP',
  'NR',
  'NU',
  'NZ',
  'OM',
  'PA',
  'PE',
  'PF',
  'PG',
  'PH',
  'PK',
  'PL',
  'PM',
  'PN',
  'PR',
  'PS',
  'PT',
  'PW',
  'PY',
  'QA',
  'RE',
  'RO',
  'RS',
  'RU',
  'RW',
  'SA',
  'SB',
  'SC',
  'SD',
  'SE',
  'SG',
  'SH',
  'SI',
  'SJ',
  'SK',
  'SL',
  'SM',
  'SN',
  'SO',
  'SR',
  'SS',
  'ST',
  'SV',
  'SX',
  'SY',
  'SZ',
  'TA',
  'TC',
  'TD',
  'TF',
  'TG',
  'TH',
  'TJ',
  'TK',
  'TL',
  'TM',
  'TN',
  'TO',
  'TR',
  'TT',
  'TV',
  'TW',
  'TZ',
  'UA',
  'UG',
  'UM',
  'US',
  'UY',
  'UZ',
  'VA',
  'VC',
  'VE',
  'VG',
  'VI',
  'VN',
  'VU',
  'WF',
  'WS',
  'XK',
  'YE',
  'YT',
  'ZA',
  'ZM',
  'ZW'
]

/**
 * The list of world regions with translated names.
 * Can be used in a <Select />
 *
 * @type {object}
 */
const REGIONS = REGION_CODES
  .sort((a, b) => trans(a, {}, 'regions') <= trans(b, {}, 'regions') ? -1 : 1)
  .reduce((regions, regionCode) => {
    regions[regionCode] = trans(regionCode, {}, 'regions')

    return regions
  }, {})

export const constants = {
  REGIONS,
  REGION_CODES,
  LANGS
}