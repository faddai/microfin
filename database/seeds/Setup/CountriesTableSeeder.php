<?php

/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 28/10/2016
 * Time: 10:52.
 */

namespace Setup;

use App\Entities\Country;
use Illuminate\Database\Seeder;

class CountriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $countries = [
            ['name' => 'Afghanistan', 'alpha_2_code' => 'AF', 'alpha_3_code' => 'AFG', 'nationality' => 'Afghan'],
            ['name' => 'Åland Islands', 'alpha_2_code' => 'AX', 'alpha_3_code' => 'ALA', 'nationality' => 'Åland Island'],
            ['name' => 'Albania', 'alpha_2_code' => 'AL', 'alpha_3_code' => 'ALB', 'nationality' => 'Albanian'],
            ['name' => 'Algeria', 'alpha_2_code' => 'DZ', 'alpha_3_code' => 'DZA', 'nationality' => 'Algerian'],
            ['name' => 'American Samoa', 'alpha_2_code' => 'AS', 'alpha_3_code' => 'ASM', 'nationality' => 'American Samoan'],
            ['name' => 'Andorra', 'alpha_2_code' => 'AD', 'alpha_3_code' => 'AND', 'nationality' => 'Andorran'],
            ['name' => 'Angola', 'alpha_2_code' => 'AO', 'alpha_3_code' => 'AGO', 'nationality' => 'Angolan'],
            ['name' => 'Anguilla', 'alpha_2_code' => 'AI', 'alpha_3_code' => 'AIA', 'nationality' => 'Anguillan'],
            ['name' => 'Antarctica', 'alpha_2_code' => 'AQ', 'alpha_3_code' => 'ATA', 'nationality' => 'Antarctic'],
            ['name' => 'Antigua and Barbuda', 'alpha_2_code' => 'AG', 'alpha_3_code' => 'ATG', 'nationality' => 'Antiguan or Barbudan'],
            ['name' => 'Argentina', 'alpha_2_code' => 'AR', 'alpha_3_code' => 'ARG', 'nationality' => 'Argentine'],
            ['name' => 'Armenia', 'alpha_2_code' => 'AM', 'alpha_3_code' => 'ARM', 'nationality' => 'Armenian'],
            ['name' => 'Aruba', 'alpha_2_code' => 'AW', 'alpha_3_code' => 'ABW', 'nationality' => 'Aruban'],
            ['name' => 'Australia', 'alpha_2_code' => 'AU', 'alpha_3_code' => 'AUS', 'nationality' => 'Australian'],
            ['name' => 'Austria', 'alpha_2_code' => 'AT', 'alpha_3_code' => 'AUT', 'nationality' => 'Austrian'],
            ['name' => 'Azerbaijan', 'alpha_2_code' => 'AZ', 'alpha_3_code' => 'AZE', 'nationality' => 'Azerbaijani, Azeri'],
            ['name' => 'Bahamas', 'alpha_2_code' => 'BS', 'alpha_3_code' => 'BHS', 'nationality' => 'Bahamian'],
            ['name' => 'Bahrain', 'alpha_2_code' => 'BH', 'alpha_3_code' => 'BHR', 'nationality' => 'Bahraini'],
            ['name' => 'Bangladesh', 'alpha_2_code' => 'BD', 'alpha_3_code' => 'BGD', 'nationality' => 'Bangladeshi'],
            ['name' => 'Barbados', 'alpha_2_code' => 'BB', 'alpha_3_code' => 'BRB', 'nationality' => 'Barbadian'],
            ['name' => 'Belarus', 'alpha_2_code' => 'BY', 'alpha_3_code' => 'BLR', 'nationality' => 'Belarusian'],
            ['name' => 'Belgium', 'alpha_2_code' => 'BE', 'alpha_3_code' => 'BEL', 'nationality' => 'Belgian'],
            ['name' => 'Belize', 'alpha_2_code' => 'BZ', 'alpha_3_code' => 'BLZ', 'nationality' => 'Belizean'],
            ['name' => 'Benin', 'alpha_2_code' => 'BJ', 'alpha_3_code' => 'BEN', 'nationality' => 'Beninese, Beninois'],
            ['name' => 'Bermuda', 'alpha_2_code' => 'BM', 'alpha_3_code' => 'BMU', 'nationality' => 'Bermudian, Bermudan'],
            ['name' => 'Bhutan', 'alpha_2_code' => 'BT', 'alpha_3_code' => 'BTN', 'nationality' => 'Bhutanese'],
            ['name' => 'Bolivia (Plurinational State of)', 'alpha_2_code' => 'BO', 'alpha_3_code' => 'BOL', 'nationality' => 'Bolivian'],
            ['name' => 'Bonaire, Sint Eustatius and Saba', 'alpha_2_code' => 'BQ', 'alpha_3_code' => 'BES', 'nationality' => 'Bonaire'],
            ['name' => 'Bosnia and Herzegovina', 'alpha_2_code' => 'BA', 'alpha_3_code' => 'BIH', 'nationality' => 'Bosnian or Herzegovinian'],
            ['name' => 'Botswana', 'alpha_2_code' => 'BW', 'alpha_3_code' => 'BWA', 'nationality' => 'Motswana, Botswanan'],
            ['name' => 'Bouvet Island', 'alpha_2_code' => 'BV', 'alpha_3_code' => 'BVT', 'nationality' => 'Bouvet Island'],
            ['name' => 'Brazil', 'alpha_2_code' => 'BR', 'alpha_3_code' => 'BRA', 'nationality' => 'Brazilian'],
            ['name' => 'British Indian Ocean Territory', 'alpha_2_code' => 'IO', 'alpha_3_code' => 'IOT', 'nationality' => 'BIOT'],
            ['name' => 'Brunei Darussalam', 'alpha_2_code' => 'BN', 'alpha_3_code' => 'BRN', 'nationality' => 'Bruneian'],
            ['name' => 'Bulgaria', 'alpha_2_code' => 'BG', 'alpha_3_code' => 'BGR', 'nationality' => 'Bulgarian'],
            ['name' => 'Burkina Faso', 'alpha_2_code' => 'BF', 'alpha_3_code' => 'BFA', 'nationality' => 'Burkinabé'],
            ['name' => 'Burundi', 'alpha_2_code' => 'BI', 'alpha_3_code' => 'BDI', 'nationality' => 'Burundian'],
            ['name' => 'Cabo Verde', 'alpha_2_code' => 'CV', 'alpha_3_code' => 'CPV', 'nationality' => 'Cabo Verdean'],
            ['name' => 'Cambodia', 'alpha_2_code' => 'KH', 'alpha_3_code' => 'KHM', 'nationality' => 'Cambodian'],
            ['name' => 'Cameroon', 'alpha_2_code' => 'CM', 'alpha_3_code' => 'CMR', 'nationality' => 'Cameroonian'],
            ['name' => 'Canada', 'alpha_2_code' => 'CA', 'alpha_3_code' => 'CAN', 'nationality' => 'Canadian'],
            ['name' => 'Cayman Islands', 'alpha_2_code' => 'KY', 'alpha_3_code' => 'CYM', 'nationality' => 'Caymanian'],
            ['name' => 'Central African Republic', 'alpha_2_code' => 'CF', 'alpha_3_code' => 'CAF', 'nationality' => 'Central African'],
            ['name' => 'Chad', 'alpha_2_code' => 'TD', 'alpha_3_code' => 'TCD', 'nationality' => 'Chadian'],
            ['name' => 'Chile', 'alpha_2_code' => 'CL', 'alpha_3_code' => 'CHL', 'nationality' => 'Chilean'],
            ['name' => 'China', 'alpha_2_code' => 'CN', 'alpha_3_code' => 'CHN', 'nationality' => 'Chinese'],
            ['name' => 'Christmas Island', 'alpha_2_code' => 'CX', 'alpha_3_code' => 'CXR', 'nationality' => 'Christmas Island'],
            ['name' => 'Cocos (Keeling) Islands', 'alpha_2_code' => 'CC', 'alpha_3_code' => 'CCK', 'nationality' => 'Cocos Island'],
            ['name' => 'Colombia', 'alpha_2_code' => 'CO', 'alpha_3_code' => 'COL', 'nationality' => 'Colombian'],
            ['name' => 'Comoros', 'alpha_2_code' => 'KM', 'alpha_3_code' => 'COM', 'nationality' => 'Comoran, Comorian'],
            ['name' => 'Congo (Republic of the)', 'alpha_2_code' => 'CG', 'alpha_3_code' => 'COG', 'nationality' => 'Congolese'],
            ['name' => 'Congo (Democratic Republic of the)', 'alpha_2_code' => 'CD', 'alpha_3_code' => 'COD', 'nationality' => 'Congolese'],
            ['name' => 'Cook Islands', 'alpha_2_code' => 'CK', 'alpha_3_code' => 'COK', 'nationality' => 'Cook Island'],
            ['name' => 'Costa Rica', 'alpha_2_code' => 'CR', 'alpha_3_code' => 'CRI', 'nationality' => 'Costa Rican'],
            ['name' => 'Côte d\'Ivoire', 'alpha_2_code' => 'CI', 'alpha_3_code' => 'CIV', 'nationality' => 'Ivorian'],
            ['name' => 'Croatia', 'alpha_2_code' => 'HR', 'alpha_3_code' => 'HRV', 'nationality' => 'Croatian'],
            ['name' => 'Cuba', 'alpha_2_code' => 'CU', 'alpha_3_code' => 'CUB', 'nationality' => 'Cuban'],
            ['name' => 'Curaçao', 'alpha_2_code' => 'CW', 'alpha_3_code' => 'CUW', 'nationality' => 'Curaçaoan'],
            ['name' => 'Cyprus', 'alpha_2_code' => 'CY', 'alpha_3_code' => 'CYP', 'nationality' => 'Cypriot'],
            ['name' => 'Czech Republic', 'alpha_2_code' => 'CZ', 'alpha_3_code' => 'CZE', 'nationality' => 'Czech'],
            ['name' => 'Denmark', 'alpha_2_code' => 'DK', 'alpha_3_code' => 'DNK', 'nationality' => 'Danish'],
            ['name' => 'Djibouti', 'alpha_2_code' => 'DJ', 'alpha_3_code' => 'DJI', 'nationality' => 'Djiboutian'],
            ['name' => 'Dominica', 'alpha_2_code' => 'DM', 'alpha_3_code' => 'DMA', 'nationality' => 'Dominican'],
            ['name' => 'Dominican Republic', 'alpha_2_code' => 'DO', 'alpha_3_code' => 'DOM', 'nationality' => 'Dominican'],
            ['name' => 'Ecuador', 'alpha_2_code' => 'EC', 'alpha_3_code' => 'ECU', 'nationality' => 'Ecuadorian'],
            ['name' => 'Egypt', 'alpha_2_code' => 'EG', 'alpha_3_code' => 'EGY', 'nationality' => 'Egyptian'],
            ['name' => 'El Salvador', 'alpha_2_code' => 'SV', 'alpha_3_code' => 'SLV', 'nationality' => 'Salvadoran'],
            ['name' => 'Equatorial Guinea', 'alpha_2_code' => 'GQ', 'alpha_3_code' => 'GNQ', 'nationality' => 'Equatorial Guinean, Equatoguinean'],
            ['name' => 'Eritrea', 'alpha_2_code' => 'ER', 'alpha_3_code' => 'ERI', 'nationality' => 'Eritrean'],
            ['name' => 'Estonia', 'alpha_2_code' => 'EE', 'alpha_3_code' => 'EST', 'nationality' => 'Estonian'],
            ['name' => 'Ethiopia', 'alpha_2_code' => 'ET', 'alpha_3_code' => 'ETH', 'nationality' => 'Ethiopian'],
            ['name' => 'Falkland Islands (Malvinas)', 'alpha_2_code' => 'FK', 'alpha_3_code' => 'FLK', 'nationality' => 'Falkland Island'],
            ['name' => 'Faroe Islands', 'alpha_2_code' => 'FO', 'alpha_3_code' => 'FRO', 'nationality' => 'Faroese'],
            ['name' => 'Fiji', 'alpha_2_code' => 'FJ', 'alpha_3_code' => 'FJI', 'nationality' => 'Fijian'],
            ['name' => 'Finland', 'alpha_2_code' => 'FI', 'alpha_3_code' => 'FIN', 'nationality' => 'Finnish'],
            ['name' => 'France', 'alpha_2_code' => 'FR', 'alpha_3_code' => 'FRA', 'nationality' => 'French'],
            ['name' => 'French Guiana', 'alpha_2_code' => 'GF', 'alpha_3_code' => 'GUF', 'nationality' => 'French Guianese'],
            ['name' => 'French Polynesia', 'alpha_2_code' => 'PF', 'alpha_3_code' => 'PYF', 'nationality' => 'French Polynesian'],
            ['name' => 'French Southern Territories', 'alpha_2_code' => 'TF', 'alpha_3_code' => 'ATF', 'nationality' => 'French Southern Territories'],
            ['name' => 'Gabon', 'alpha_2_code' => 'GA', 'alpha_3_code' => 'GAB', 'nationality' => 'Gabonese'],
            ['name' => 'Gambia', 'alpha_2_code' => 'GM', 'alpha_3_code' => 'GMB', 'nationality' => 'Gambian'],
            ['name' => 'Georgia', 'alpha_2_code' => 'GE', 'alpha_3_code' => 'GEO', 'nationality' => 'Georgian'],
            ['name' => 'Germany', 'alpha_2_code' => 'DE', 'alpha_3_code' => 'DEU', 'nationality' => 'German'],
            ['name' => 'Ghana', 'alpha_2_code' => 'GH', 'alpha_3_code' => 'GHA', 'nationality' => 'Ghanaian'],
            ['name' => 'Gibraltar', 'alpha_2_code' => 'GI', 'alpha_3_code' => 'GIB', 'nationality' => 'Gibraltar'],
            ['name' => 'Greece', 'alpha_2_code' => 'GR', 'alpha_3_code' => 'GRC', 'nationality' => 'Greek, Hellenic'],
            ['name' => 'Greenland', 'alpha_2_code' => 'GL', 'alpha_3_code' => 'GRL', 'nationality' => 'Greenlandic'],
            ['name' => 'Grenada', 'alpha_2_code' => 'GD', 'alpha_3_code' => 'GRD', 'nationality' => 'Grenadian'],
            ['name' => 'Guadeloupe', 'alpha_2_code' => 'GP', 'alpha_3_code' => 'GLP', 'nationality' => 'Guadeloupe'],
            ['name' => 'Guam', 'alpha_2_code' => 'GU', 'alpha_3_code' => 'GUM', 'nationality' => 'Guamanian, Guambat'],
            ['name' => 'Guatemala', 'alpha_2_code' => 'GT', 'alpha_3_code' => 'GTM', 'nationality' => 'Guatemalan'],
            ['name' => 'Guernsey', 'alpha_2_code' => 'GG', 'alpha_3_code' => 'GGY', 'nationality' => 'Channel Island'],
            ['name' => 'Guinea', 'alpha_2_code' => 'GN', 'alpha_3_code' => 'GIN', 'nationality' => 'Guinean'],
            ['name' => 'Guinea-Bissau', 'alpha_2_code' => 'GW', 'alpha_3_code' => 'GNB', 'nationality' => 'Bissau-Guinean'],
            ['name' => 'Guyana', 'alpha_2_code' => 'GY', 'alpha_3_code' => 'GUY', 'nationality' => 'Guyanese'],
            ['name' => 'Haiti', 'alpha_2_code' => 'HT', 'alpha_3_code' => 'HTI', 'nationality' => 'Haitian'],
            ['name' => 'Heard Island and McDonald Islands', 'alpha_2_code' => 'HM', 'alpha_3_code' => 'HMD', 'nationality' => 'Heard Island or McDonald Islands'],
            ['name' => 'Vatican City State', 'alpha_2_code' => 'VA', 'alpha_3_code' => 'VAT', 'nationality' => 'Vatican'],
            ['name' => 'Honduras', 'alpha_2_code' => 'HN', 'alpha_3_code' => 'HND', 'nationality' => 'Honduran'],
            ['name' => 'Hong Kong', 'alpha_2_code' => 'HK', 'alpha_3_code' => 'HKG', 'nationality' => 'Hong Kong, Hong Kongese'],
            ['name' => 'Hungary', 'alpha_2_code' => 'HU', 'alpha_3_code' => 'HUN', 'nationality' => 'Hungarian, Magyar'],
            ['name' => 'Iceland', 'alpha_2_code' => 'IS', 'alpha_3_code' => 'ISL', 'nationality' => 'Icelandic'],
            ['name' => 'India', 'alpha_2_code' => 'IN', 'alpha_3_code' => 'IND', 'nationality' => 'Indian'],
            ['name' => 'Indonesia', 'alpha_2_code' => 'ID', 'alpha_3_code' => 'IDN', 'nationality' => 'Indonesian'],
            ['name' => 'Iran', 'alpha_2_code' => 'IR', 'alpha_3_code' => 'IRN', 'nationality' => 'Iranian, Persian'],
            ['name' => 'Iraq', 'alpha_2_code' => 'IQ', 'alpha_3_code' => 'IRQ', 'nationality' => 'Iraqi'],
            ['name' => 'Ireland', 'alpha_2_code' => 'IE', 'alpha_3_code' => 'IRL', 'nationality' => 'Irish'],
            ['name' => 'Isle of Man', 'alpha_2_code' => 'IM', 'alpha_3_code' => 'IMN', 'nationality' => 'Manx'],
            ['name' => 'Israel', 'alpha_2_code' => 'IL', 'alpha_3_code' => 'ISR', 'nationality' => 'Israeli'],
            ['name' => 'Italy', 'alpha_2_code' => 'IT', 'alpha_3_code' => 'ITA', 'nationality' => 'Italian'],
            ['name' => 'Jamaica', 'alpha_2_code' => 'JM', 'alpha_3_code' => 'JAM', 'nationality' => 'Jamaican'],
            ['name' => 'Japan', 'alpha_2_code' => 'JP', 'alpha_3_code' => 'JPN', 'nationality' => 'Japanese'],
            ['name' => 'Jersey', 'alpha_2_code' => 'JE', 'alpha_3_code' => 'JEY', 'nationality' => 'Channel Island'],
            ['name' => 'Jordan', 'alpha_2_code' => 'JO', 'alpha_3_code' => 'JOR', 'nationality' => 'Jordanian'],
            ['name' => 'Kazakhstan', 'alpha_2_code' => 'KZ', 'alpha_3_code' => 'KAZ', 'nationality' => 'Kazakhstani, Kazakh'],
            ['name' => 'Kenya', 'alpha_2_code' => 'KE', 'alpha_3_code' => 'KEN', 'nationality' => 'Kenyan'],
            ['name' => 'Kiribati', 'alpha_2_code' => 'KI', 'alpha_3_code' => 'KIR', 'nationality' => 'I-Kiribati'],
            ['name' => 'Korea (Democratic People\'s Republic of)', 'alpha_2_code' => 'KP', 'alpha_3_code' => 'PRK', 'nationality' => 'North Korean'],
            ['name' => 'Korea (Republic of)', 'alpha_2_code' => 'KR', 'alpha_3_code' => 'KOR', 'nationality' => 'South Korean'],
            ['name' => 'Kuwait', 'alpha_2_code' => 'KW', 'alpha_3_code' => 'KWT', 'nationality' => 'Kuwaiti'],
            ['name' => 'Kyrgyzstan', 'alpha_2_code' => 'KG', 'alpha_3_code' => 'KGZ', 'nationality' => 'Kyrgyzstani, Kyrgyz, Kirgiz, Kirghiz'],
            ['name' => 'Lao People\'s Democratic Republic', 'alpha_2_code' => 'LA', 'alpha_3_code' => 'LAO', 'nationality' => 'Lao, Laotian'],
            ['name' => 'Latvia', 'alpha_2_code' => 'LV', 'alpha_3_code' => 'LVA', 'nationality' => 'Latvian'],
            ['name' => 'Lebanon', 'alpha_2_code' => 'LB', 'alpha_3_code' => 'LBN', 'nationality' => 'Lebanese'],
            ['name' => 'Lesotho', 'alpha_2_code' => 'LS', 'alpha_3_code' => 'LSO', 'nationality' => 'Basotho'],
            ['name' => 'Liberia', 'alpha_2_code' => 'LR', 'alpha_3_code' => 'LBR', 'nationality' => 'Liberian'],
            ['name' => 'Libya', 'alpha_2_code' => 'LY', 'alpha_3_code' => 'LBY', 'nationality' => 'Libyan'],
            ['name' => 'Liechtenstein', 'alpha_2_code' => 'LI', 'alpha_3_code' => 'LIE', 'nationality' => 'Liechtenstein'],
            ['name' => 'Lithuania', 'alpha_2_code' => 'LT', 'alpha_3_code' => 'LTU', 'nationality' => 'Lithuanian'],
            ['name' => 'Luxembourg', 'alpha_2_code' => 'LU', 'alpha_3_code' => 'LUX', 'nationality' => 'Luxembourg, Luxembourgish'],
            ['name' => 'Macao', 'alpha_2_code' => 'MO', 'alpha_3_code' => 'MAC', 'nationality' => 'Macanese, Chinese'],
            ['name' => 'Macedonia (the former Yugoslav Republic of)', 'alpha_2_code' => 'MK', 'alpha_3_code' => 'MKD', 'nationality' => 'Macedonian'],
            ['name' => 'Madagascar', 'alpha_2_code' => 'MG', 'alpha_3_code' => 'MDG', 'nationality' => 'Malagasy'],
            ['name' => 'Malawi', 'alpha_2_code' => 'MW', 'alpha_3_code' => 'MWI', 'nationality' => 'Malawian'],
            ['name' => 'Malaysia', 'alpha_2_code' => 'MY', 'alpha_3_code' => 'MYS', 'nationality' => 'Malaysian'],
            ['name' => 'Maldives', 'alpha_2_code' => 'MV', 'alpha_3_code' => 'MDV', 'nationality' => 'Maldivian'],
            ['name' => 'Mali', 'alpha_2_code' => 'ML', 'alpha_3_code' => 'MLI', 'nationality' => 'Malian, Malinese'],
            ['name' => 'Malta', 'alpha_2_code' => 'MT', 'alpha_3_code' => 'MLT', 'nationality' => 'Maltese'],
            ['name' => 'Marshall Islands', 'alpha_2_code' => 'MH', 'alpha_3_code' => 'MHL', 'nationality' => 'Marshallese'],
            ['name' => 'Martinique', 'alpha_2_code' => 'MQ', 'alpha_3_code' => 'MTQ', 'nationality' => 'Martiniquais, Martinican'],
            ['name' => 'Mauritania', 'alpha_2_code' => 'MR', 'alpha_3_code' => 'MRT', 'nationality' => 'Mauritanian'],
            ['name' => 'Mauritius', 'alpha_2_code' => 'MU', 'alpha_3_code' => 'MUS', 'nationality' => 'Mauritian'],
            ['name' => 'Mayotte', 'alpha_2_code' => 'YT', 'alpha_3_code' => 'MYT', 'nationality' => 'Mahoran'],
            ['name' => 'Mexico', 'alpha_2_code' => 'MX', 'alpha_3_code' => 'MEX', 'nationality' => 'Mexican'],
            ['name' => 'Micronesia (Federated States of)', 'alpha_2_code' => 'FM', 'alpha_3_code' => 'FSM', 'nationality' => 'Micronesian'],
            ['name' => 'Moldova (Republic of)', 'alpha_2_code' => 'MD', 'alpha_3_code' => 'MDA', 'nationality' => 'Moldovan'],
            ['name' => 'Monaco', 'alpha_2_code' => 'MC', 'alpha_3_code' => 'MCO', 'nationality' => 'Monégasque, Monacan'],
            ['name' => 'Mongolia', 'alpha_2_code' => 'MN', 'alpha_3_code' => 'MNG', 'nationality' => 'Mongolian'],
            ['name' => 'Montenegro', 'alpha_2_code' => 'ME', 'alpha_3_code' => 'MNE', 'nationality' => 'Montenegrin'],
            ['name' => 'Montserrat', 'alpha_2_code' => 'MS', 'alpha_3_code' => 'MSR', 'nationality' => 'Montserratian'],
            ['name' => 'Morocco', 'alpha_2_code' => 'MA', 'alpha_3_code' => 'MAR', 'nationality' => 'Moroccan'],
            ['name' => 'Mozambique', 'alpha_2_code' => 'MZ', 'alpha_3_code' => 'MOZ', 'nationality' => 'Mozambican'],
            ['name' => 'Myanmar', 'alpha_2_code' => 'MM', 'alpha_3_code' => 'MMR', 'nationality' => 'Burmese'],
            ['name' => 'Namibia', 'alpha_2_code' => 'NA', 'alpha_3_code' => 'NAM', 'nationality' => 'Namibian'],
            ['name' => 'Nauru', 'alpha_2_code' => 'NR', 'alpha_3_code' => 'NRU', 'nationality' => 'Nauruan'],
            ['name' => 'Nepal', 'alpha_2_code' => 'NP', 'alpha_3_code' => 'NPL', 'nationality' => 'Nepali, Nepalese'],
            ['name' => 'Netherlands', 'alpha_2_code' => 'NL', 'alpha_3_code' => 'NLD', 'nationality' => 'Dutch, Netherlandic'],
            ['name' => 'New Caledonia', 'alpha_2_code' => 'NC', 'alpha_3_code' => 'NCL', 'nationality' => 'New Caledonian'],
            ['name' => 'New Zealand', 'alpha_2_code' => 'NZ', 'alpha_3_code' => 'NZL', 'nationality' => 'New Zealand, NZ'],
            ['name' => 'Nicaragua', 'alpha_2_code' => 'NI', 'alpha_3_code' => 'NIC', 'nationality' => 'Nicaraguan'],
            ['name' => 'Niger', 'alpha_2_code' => 'NE', 'alpha_3_code' => 'NER', 'nationality' => 'Nigerien'],
            ['name' => 'Nigeria', 'alpha_2_code' => 'NG', 'alpha_3_code' => 'NGA', 'nationality' => 'Nigerian'],
            ['name' => 'Niue', 'alpha_2_code' => 'NU', 'alpha_3_code' => 'NIU', 'nationality' => 'Niuean'],
            ['name' => 'Norfolk Island', 'alpha_2_code' => 'NF', 'alpha_3_code' => 'NFK', 'nationality' => 'Norfolk Island'],
            ['name' => 'Northern Mariana Islands', 'alpha_2_code' => 'MP', 'alpha_3_code' => 'MNP', 'nationality' => 'Northern Marianan'],
            ['name' => 'Norway', 'alpha_2_code' => 'NO', 'alpha_3_code' => 'NOR', 'nationality' => 'Norwegian'],
            ['name' => 'Oman', 'alpha_2_code' => 'OM', 'alpha_3_code' => 'OMN', 'nationality' => 'Omani'],
            ['name' => 'Pakistan', 'alpha_2_code' => 'PK', 'alpha_3_code' => 'PAK', 'nationality' => 'Pakistani'],
            ['name' => 'Palau', 'alpha_2_code' => 'PW', 'alpha_3_code' => 'PLW', 'nationality' => 'Palauan'],
            ['name' => 'Palestine, State of', 'alpha_2_code' => 'PS', 'alpha_3_code' => 'PSE', 'nationality' => 'Palestinian'],
            ['name' => 'Panama', 'alpha_2_code' => 'PA', 'alpha_3_code' => 'PAN', 'nationality' => 'Panamanian'],
            ['name' => 'Papua New Guinea', 'alpha_2_code' => 'PG', 'alpha_3_code' => 'PNG', 'nationality' => 'Papua New Guinean, Papuan'],
            ['name' => 'Paraguay', 'alpha_2_code' => 'PY', 'alpha_3_code' => 'PRY', 'nationality' => 'Paraguayan'],
            ['name' => 'Peru', 'alpha_2_code' => 'PE', 'alpha_3_code' => 'PER', 'nationality' => 'Peruvian'],
            ['name' => 'Philippines', 'alpha_2_code' => 'PH', 'alpha_3_code' => 'PHL', 'nationality' => 'Philippine, Filipino'],
            ['name' => 'Pitcairn', 'alpha_2_code' => 'PN', 'alpha_3_code' => 'PCN', 'nationality' => 'Pitcairn Island'],
            ['name' => 'Poland', 'alpha_2_code' => 'PL', 'alpha_3_code' => 'POL', 'nationality' => 'Polish'],
            ['name' => 'Portugal', 'alpha_2_code' => 'PT', 'alpha_3_code' => 'PRT', 'nationality' => 'Portuguese'],
            ['name' => 'Puerto Rico', 'alpha_2_code' => 'PR', 'alpha_3_code' => 'PRI', 'nationality' => 'Puerto Rican'],
            ['name' => 'Qatar', 'alpha_2_code' => 'QA', 'alpha_3_code' => 'QAT', 'nationality' => 'Qatari'],
            ['name' => 'Réunion', 'alpha_2_code' => 'RE', 'alpha_3_code' => 'REU', 'nationality' => 'Réunionese, Réunionnais'],
            ['name' => 'Romania', 'alpha_2_code' => 'RO', 'alpha_3_code' => 'ROU', 'nationality' => 'Romanian'],
            ['name' => 'Russian Federation', 'alpha_2_code' => 'RU', 'alpha_3_code' => 'RUS', 'nationality' => 'Russian'],
            ['name' => 'Rwanda', 'alpha_2_code' => 'RW', 'alpha_3_code' => 'RWA', 'nationality' => 'Rwandan'],
            ['name' => 'Saint Barthélemy', 'alpha_2_code' => 'BL', 'alpha_3_code' => 'BLM', 'nationality' => 'Barthélemois'],
            ['name' => 'Saint Helena, Ascension and Tristan da Cunha', 'alpha_2_code' => 'SH', 'alpha_3_code' => 'SHN', 'nationality' => 'Saint Helenian'],
            ['name' => 'Saint Kitts and Nevis', 'alpha_2_code' => 'KN', 'alpha_3_code' => 'KNA', 'nationality' => 'Kittitian or Nevisian'],
            ['name' => 'Saint Lucia', 'alpha_2_code' => 'LC', 'alpha_3_code' => 'LCA', 'nationality' => 'Saint Lucian'],
            ['name' => 'Saint Martin (French part)', 'alpha_2_code' => 'MF', 'alpha_3_code' => 'MAF', 'nationality' => 'Saint-Martinoise'],
            ['name' => 'Saint Pierre and Miquelon', 'alpha_2_code' => 'PM', 'alpha_3_code' => 'SPM', 'nationality' => 'Saint-Pierrais or Miquelonnais'],
            ['name' => 'Saint Vincent and the Grenadines', 'alpha_2_code' => 'VC', 'alpha_3_code' => 'VCT', 'nationality' => 'Saint Vincentian, Vincentian'],
            ['name' => 'Samoa', 'alpha_2_code' => 'WS', 'alpha_3_code' => 'WSM', 'nationality' => 'Samoan'],
            ['name' => 'San Marino', 'alpha_2_code' => 'SM', 'alpha_3_code' => 'SMR', 'nationality' => 'Sammarinese'],
            ['name' => 'Sao Tome and Principe', 'alpha_2_code' => 'ST', 'alpha_3_code' => 'STP', 'nationality' => 'São Toméan'],
            ['name' => 'Saudi Arabia', 'alpha_2_code' => 'SA', 'alpha_3_code' => 'SAU', 'nationality' => 'Saudi, Saudi Arabian'],
            ['name' => 'Senegal', 'alpha_2_code' => 'SN', 'alpha_3_code' => 'SEN', 'nationality' => 'Senegalese'],
            ['name' => 'Serbia', 'alpha_2_code' => 'RS', 'alpha_3_code' => 'SRB', 'nationality' => 'Serbian'],
            ['name' => 'Seychelles', 'alpha_2_code' => 'SC', 'alpha_3_code' => 'SYC', 'nationality' => 'Seychellois'],
            ['name' => 'Sierra Leone', 'alpha_2_code' => 'SL', 'alpha_3_code' => 'SLE', 'nationality' => 'Sierra Leonean'],
            ['name' => 'Singapore', 'alpha_2_code' => 'SG', 'alpha_3_code' => 'SGP', 'nationality' => 'Singaporean'],
            ['name' => 'Sint Maarten (Dutch part)', 'alpha_2_code' => 'SX', 'alpha_3_code' => 'SXM', 'nationality' => 'Sint Maarten'],
            ['name' => 'Slovakia', 'alpha_2_code' => 'SK', 'alpha_3_code' => 'SVK', 'nationality' => 'Slovak'],
            ['name' => 'Slovenia', 'alpha_2_code' => 'SI', 'alpha_3_code' => 'SVN', 'nationality' => 'Slovenian, Slovene'],
            ['name' => 'Solomon Islands', 'alpha_2_code' => 'SB', 'alpha_3_code' => 'SLB', 'nationality' => 'Solomon Island'],
            ['name' => 'Somalia', 'alpha_2_code' => 'SO', 'alpha_3_code' => 'SOM', 'nationality' => 'Somali, Somalian'],
            ['name' => 'South Africa', 'alpha_2_code' => 'ZA', 'alpha_3_code' => 'ZAF', 'nationality' => 'South African'],
            ['name' => 'South Georgia and the South Sandwich Islands', 'alpha_2_code' => 'GS', 'alpha_3_code' => 'SGS', 'nationality' => 'South Georgia or South Sandwich Islands'],
            ['name' => 'South Sudan', 'alpha_2_code' => 'SS', 'alpha_3_code' => 'SSD', 'nationality' => 'South Sudanese'],
            ['name' => 'Spain', 'alpha_2_code' => 'ES', 'alpha_3_code' => 'ESP', 'nationality' => 'Spanish'],
            ['name' => 'Sri Lanka', 'alpha_2_code' => 'LK', 'alpha_3_code' => 'LKA', 'nationality' => 'Sri Lankan'],
            ['name' => 'Sudan', 'alpha_2_code' => 'SD', 'alpha_3_code' => 'SDN', 'nationality' => 'Sudanese'],
            ['name' => 'Suriname', 'alpha_2_code' => 'SR', 'alpha_3_code' => 'SUR', 'nationality' => 'Surinamese'],
            ['name' => 'Svalbard and Jan Mayen', 'alpha_2_code' => 'SJ', 'alpha_3_code' => 'SJM', 'nationality' => 'Svalbard'],
            ['name' => 'Swaziland', 'alpha_2_code' => 'SZ', 'alpha_3_code' => 'SWZ', 'nationality' => 'Swazi'],
            ['name' => 'Sweden', 'alpha_2_code' => 'SE', 'alpha_3_code' => 'SWE', 'nationality' => 'Swedish'],
            ['name' => 'Switzerland', 'alpha_2_code' => 'CH', 'alpha_3_code' => 'CHE', 'nationality' => 'Swiss'],
            ['name' => 'Syrian Arab Republic', 'alpha_2_code' => 'SY', 'alpha_3_code' => 'SYR', 'nationality' => 'Syrian'],
            ['name' => 'Taiwan, Province of China', 'alpha_2_code' => 'TW', 'alpha_3_code' => 'TWN', 'nationality' => 'Chinese, Taiwanese'],
            ['name' => 'Tajikistan', 'alpha_2_code' => 'TJ', 'alpha_3_code' => 'TJK', 'nationality' => 'Tajikistani'],
            ['name' => 'Tanzania, United Republic of', 'alpha_2_code' => 'TZ', 'alpha_3_code' => 'TZA', 'nationality' => 'Tanzanian'],
            ['name' => 'Thailand', 'alpha_2_code' => 'TH', 'alpha_3_code' => 'THA', 'nationality' => 'Thai'],
            ['name' => 'Timor-Leste', 'alpha_2_code' => 'TL', 'alpha_3_code' => 'TLS', 'nationality' => 'Timorese'],
            ['name' => 'Togo', 'alpha_2_code' => 'TG', 'alpha_3_code' => 'TGO', 'nationality' => 'Togolese'],
            ['name' => 'Tokelau', 'alpha_2_code' => 'TK', 'alpha_3_code' => 'TKL', 'nationality' => 'Tokelauan'],
            ['name' => 'Tonga', 'alpha_2_code' => 'TO', 'alpha_3_code' => 'TON', 'nationality' => 'Tongan'],
            ['name' => 'Trinidad and Tobago', 'alpha_2_code' => 'TT', 'alpha_3_code' => 'TTO', 'nationality' => 'Trinidadian or Tobagonian'],
            ['name' => 'Tunisia', 'alpha_2_code' => 'TN', 'alpha_3_code' => 'TUN', 'nationality' => 'Tunisian'],
            ['name' => 'Turkey', 'alpha_2_code' => 'TR', 'alpha_3_code' => 'TUR', 'nationality' => 'Turkish'],
            ['name' => 'Turkmenistan', 'alpha_2_code' => 'TM', 'alpha_3_code' => 'TKM', 'nationality' => 'Turkmen'],
            ['name' => 'Turks and Caicos Islands', 'alpha_2_code' => 'TC', 'alpha_3_code' => 'TCA', 'nationality' => 'Turks and Caicos Island'],
            ['name' => 'Tuvalu', 'alpha_2_code' => 'TV', 'alpha_3_code' => 'TUV', 'nationality' => 'Tuvaluan'],
            ['name' => 'Uganda', 'alpha_2_code' => 'UG', 'alpha_3_code' => 'UGA', 'nationality' => 'Ugandan'],
            ['name' => 'Ukraine', 'alpha_2_code' => 'UA', 'alpha_3_code' => 'UKR', 'nationality' => 'Ukrainian'],
            ['name' => 'United Arab Emirates', 'alpha_2_code' => 'AE', 'alpha_3_code' => 'ARE', 'nationality' => 'Emirati, Emirian, Emiri'],
            ['name' => 'United Kingdom of Great Britain and Northern Ireland', 'alpha_2_code' => 'GB', 'alpha_3_code' => 'GBR', 'nationality' => 'British, UK'],
            ['name' => 'United States Minor Outlying Islands', 'alpha_2_code' => 'UM', 'alpha_3_code' => 'UMI', 'nationality' => 'American'],
            ['name' => 'United States of America', 'alpha_2_code' => 'US', 'alpha_3_code' => 'USA', 'nationality' => 'American'],
            ['name' => 'Uruguay', 'alpha_2_code' => 'UY', 'alpha_3_code' => 'URY', 'nationality' => 'Uruguayan'],
            ['name' => 'Uzbekistan', 'alpha_2_code' => 'UZ', 'alpha_3_code' => 'UZB', 'nationality' => 'Uzbekistani, Uzbek'],
            ['name' => 'Vanuatu', 'alpha_2_code' => 'VU', 'alpha_3_code' => 'VUT', 'nationality' => 'Ni-Vanuatu, Vanuatuan'],
            ['name' => 'Venezuela (Bolivarian Republic of)', 'alpha_2_code' => 'VE', 'alpha_3_code' => 'VEN', 'nationality' => 'Venezuelan'],
            ['name' => 'Vietnam', 'alpha_2_code' => 'VN', 'alpha_3_code' => 'VNM', 'nationality' => 'Vietnamese'],
            ['name' => 'Virgin Islands (British)', 'alpha_2_code' => 'VG', 'alpha_3_code' => 'VGB', 'nationality' => 'British Virgin Island'],
            ['name' => 'Virgin Islands (U.S.)', 'alpha_2_code' => 'VI', 'alpha_3_code' => 'VIR', 'nationality' => 'U.S. Virgin Island'],
            ['name' => 'Wallis and Futuna', 'alpha_2_code' => 'WF', 'alpha_3_code' => 'WLF', 'nationality' => 'Wallis and Futuna, Wallisian or Futunan'],
            ['name' => 'Western Sahara', 'alpha_2_code' => 'EH', 'alpha_3_code' => 'ESH', 'nationality' => 'Sahrawi, Sahrawian, Sahraouian'],
            ['name' => 'Yemen', 'alpha_2_code' => 'YE', 'alpha_3_code' => 'YEM', 'nationality' => 'Yemeni'],
            ['name' => 'Zambia', 'alpha_2_code' => 'ZM', 'alpha_3_code' => 'ZMB', 'nationality' => 'Zambian'],
            ['name' => 'Zimbabwe', 'alpha_2_code' => 'ZW', 'alpha_3_code' => 'ZWE', 'nationality' => 'Zimbabwean'],
        ];

        foreach ($countries as $country) {
            Country::firstOrCreate($country);
        }
    }
}
