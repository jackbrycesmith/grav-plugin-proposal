<?php
namespace Grav\Plugin;

require_once __DIR__ . '/vendor/autoload.php';

use Grav\Common\Plugin;
use RocketTheme\Toolbox\Event\Event;
use Grav\Common\Uri;
use Omnipay\Omnipay;
use RocketTheme\Toolbox\File\File;
use Symfony\Component\Yaml\Yaml;

/**
 * Class ProposalPlugin
 * @package Grav\Plugin
 */
class ProposalPlugin extends Plugin
{
    /**
     * @return array
     *
     * The getSubscribedEvents() gives the core a list of events
     *     that the plugin wants to listen to. The key of each
     *     array section is the event that the plugin listens to
     *     and the value (in the form of an array) contains the
     *     callable (or function) as well as the priority. The
     *     higher the number the higher the priority.
     */
    public static function getSubscribedEvents()
    {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0],
            'onTwigTemplatePaths' => ['onTwigTemplatePaths', 0],
            'onTwigSiteVariables' => ['onTwigSiteVariables', 0]
        ];
    }

    /**
     * Initialize the plugin
     */
    public function onPluginsInitialized()
    {
        // Don't proceed if we are in the admin plugin, but add blueprint stuff
        if ($this->isAdmin()) {
            $this->enable([
                'onGetPageTemplates' => ['onGetPageTemplates', 0],
            ]);
            return;
        }

        $uri = $this->grav['uri'];
        $route = $this->config->get('plugins.proposal.pay_route');

        if ($route && $route == $uri->path()) {
            $this->enable([
                'onPageInitialized' => ['onPageInitialized', 0],
            ]);
        } 
    }

    public function onTwigSiteVariables() {
        // only look up proposal data for proposal pages
        if ($this->grav['page']->template() == 'proposal') {
            $this->grav['twig']->twig_vars['proposal'] = $this->fetchProposal();
            $this->grav['twig']->twig_vars['currency_symbol'] = $this->getSymbolOfCurrencyCode($this->config->get('plugins.proposal.stripe_currency'));
        }
    }


    /**
     * Add current directory to twig lookup paths.
     */
    public function onTwigTemplatePaths()
    {
        $this->grav['twig']->twig_paths[] = __DIR__ . '/templates';
    }

    
    /**
     * Handle route for paying proposal deposit
     */
    public function onPageInitialized()
    {        
        $_POST = array_merge($_POST, (array) json_decode(file_get_contents('php://input')));
        
        if (!$_POST) {
            return;
        }

        if (isset($_POST['token'], $_POST['path'], $_POST['amount'], $_POST['currency'])) {
            $token = filter_var($_POST['token'], FILTER_SANITIZE_STRING);
            $path = filter_var($_POST['path'], FILTER_SANITIZE_STRING);
            $amount = filter_var($_POST['amount'], FILTER_SANITIZE_STRING);
            $currency = filter_var($_POST['currency'], FILTER_SANITIZE_STRING);

            // stop charge attempt of proposal acceptance already exists

            $fileCheck = $path . '.yaml';
            $proposalLookup = File::instance(DATA_DIR . 'proposal/' . $fileCheck);

            if ($proposalLookup->content()) {
                // Ok this proposal has already been accepted, so don't try and charge again
                header('HTTP/1.0 400 Bad Request');
                exit;
            }
            
            // attempt to make charge with stripe

            $this->proposalCharge($token, $amount, $currency);

            // store proposal acceptance data

            $ext = '.yaml';
            $filename = $path . $ext;
            $body = Yaml::dump([
                'deposit'    => $amount,
                'currency'   => $currency,
                'paid_on'    => time(),
            ]);
            $file = File::instance(DATA_DIR . 'proposal' . '/' . $filename);
            $file->save($body);
            
            header('HTTP/1.0 200 OK');
            exit;
        } else {
            header('HTTP/1.0 400 Bad Request');
            exit;
        }
    }

    
    /**
     * Handle paying deposit with stripe
     *
     */
    public function proposalCharge($token, $amount, $currency)
    {
        $description = 'Deposit';
        $secretKey = $this->grav['config']->get('plugins.proposal.stripe_secret_key');

        $gateway = Omnipay::create('Stripe');
        $gateway->setApiKey($secretKey);

        try {
            $response = $gateway->purchase([
                'amount' => $amount,
                'currency' => $currency,
                'description' => $description,
                'token' => $token])->send();

            if ($response->isSuccessful()) {
                // 
            } else {
                // display error
                throw new \RuntimeException("Payment not successful: " . $response->getMessage());
                $this->grav['log']->info('failed payment');
            }
        } catch (\Exception $e) {
            // internal error, log exception
            $this->grav['log']->info('failed payment');
            throw new \RuntimeException('Sorry, there was an error processing your payment: ' . $e->getMessage());
        }
    }    

    /**
     * Return the proposal acceptance data associated to the current route
     */
    private function fetchProposal() {

        $lang = $this->grav['language']->getLanguage();
        $filename = $lang ? '/' . $lang : '';
        $filename .= $this->grav['uri']->path() . '.yaml';

        $proposal = $this->getDataFromFilename($filename);

        return $proposal;
    }

    /**
     * Given a data file route, return the YAML content already parsed
     */
    private function getDataFromFilename($fileRoute) {

        //Single item details
        $fileInstance = File::instance(DATA_DIR . 'proposal/' . $fileRoute);

        if (!$fileInstance->content()) {
            //Item not found
            return;
        }

        return Yaml::parse($fileInstance->content());
    }

    /**
     * Make the proposals manageable through the admin plugin
     */
    public function onGetPageTemplates($event) {
        $types = $event->types;
        $locator = $this->grav['locator'];
        $types->scanBlueprints($locator->findResource('plugin://' . $this->name . '/blueprints'));
        $types->scanTemplates($locator->findResource('plugin://' . $this->name . '/templates'));
    }

     /**
     * Returns the symbol of the passed currency code
     *
     * https://gist.github.com/Gibbs/3920259
     * 
     */
    public static function getSymbolOfCurrencyCode($currencyCode) {
        $currencyCode = strtoupper($currencyCode);
        $currencies = [
            	'AED' => '&#1583;.&#1573;', // ?
                'AFN' => '&#65;&#102;',
                'ALL' => '&#76;&#101;&#107;',
                'AMD' => '',
                'ANG' => '&#402;',
                'AOA' => '&#75;&#122;', // ?
                'ARS' => '&#36;',
                'AUD' => '&#36;',
                'AWG' => '&#402;',
                'AZN' => '&#1084;&#1072;&#1085;',
                'BAM' => '&#75;&#77;',
                'BBD' => '&#36;',
                'BDT' => '&#2547;', // ?
                'BGN' => '&#1083;&#1074;',
                'BHD' => '.&#1583;.&#1576;', // ?
                'BIF' => '&#70;&#66;&#117;', // ?
                'BMD' => '&#36;',
                'BND' => '&#36;',
                'BOB' => '&#36;&#98;',
                'BRL' => '&#82;&#36;',
                'BSD' => '&#36;',
                'BTN' => '&#78;&#117;&#46;', // ?
                'BWP' => '&#80;',
                'BYR' => '&#112;&#46;',
                'BZD' => '&#66;&#90;&#36;',
                'CAD' => '&#36;',
                'CDF' => '&#70;&#67;',
                'CHF' => '&#67;&#72;&#70;',
                'CLF' => '', // ?
                'CLP' => '&#36;',
                'CNY' => '&#165;',
                'COP' => '&#36;',
                'CRC' => '&#8353;',
                'CUP' => '&#8396;',
                'CVE' => '&#36;', // ?
                'CZK' => '&#75;&#269;',
                'DJF' => '&#70;&#100;&#106;', // ?
                'DKK' => '&#107;&#114;',
                'DOP' => '&#82;&#68;&#36;',
                'DZD' => '&#1583;&#1580;', // ?
                'EGP' => '&#163;',
                'ETB' => '&#66;&#114;',
                'EUR' => '&#8364;',
                'FJD' => '&#36;',
                'FKP' => '&#163;',
                'GBP' => '&#163;',
                'GEL' => '&#4314;', // ?
                'GHS' => '&#162;',
                'GIP' => '&#163;',
                'GMD' => '&#68;', // ?
                'GNF' => '&#70;&#71;', // ?
                'GTQ' => '&#81;',
                'GYD' => '&#36;',
                'HKD' => '&#36;',
                'HNL' => '&#76;',
                'HRK' => '&#107;&#110;',
                'HTG' => '&#71;', // ?
                'HUF' => '&#70;&#116;',
                'IDR' => '&#82;&#112;',
                'ILS' => '&#8362;',
                'INR' => '&#8377;',
                'IQD' => '&#1593;.&#1583;', // ?
                'IRR' => '&#65020;',
                'ISK' => '&#107;&#114;',
                'JEP' => '&#163;',
                'JMD' => '&#74;&#36;',
                'JOD' => '&#74;&#68;', // ?
                'JPY' => '&#165;',
                'KES' => '&#75;&#83;&#104;', // ?
                'KGS' => '&#1083;&#1074;',
                'KHR' => '&#6107;',
                'KMF' => '&#67;&#70;', // ?
                'KPW' => '&#8361;',
                'KRW' => '&#8361;',
                'KWD' => '&#1583;.&#1603;', // ?
                'KYD' => '&#36;',
                'KZT' => '&#1083;&#1074;',
                'LAK' => '&#8365;',
                'LBP' => '&#163;',
                'LKR' => '&#8360;',
                'LRD' => '&#36;',
                'LSL' => '&#76;', // ?
                'LTL' => '&#76;&#116;',
                'LVL' => '&#76;&#115;',
                'LYD' => '&#1604;.&#1583;', // ?
                'MAD' => '&#1583;.&#1605;.', //?
                'MDL' => '&#76;',
                'MGA' => '&#65;&#114;', // ?
                'MKD' => '&#1076;&#1077;&#1085;',
                'MMK' => '&#75;',
                'MNT' => '&#8366;',
                'MOP' => '&#77;&#79;&#80;&#36;', // ?
                'MRO' => '&#85;&#77;', // ?
                'MUR' => '&#8360;', // ?
                'MVR' => '.&#1923;', // ?
                'MWK' => '&#77;&#75;',
                'MXN' => '&#36;',
                'MYR' => '&#82;&#77;',
                'MZN' => '&#77;&#84;',
                'NAD' => '&#36;',
                'NGN' => '&#8358;',
                'NIO' => '&#67;&#36;',
                'NOK' => '&#107;&#114;',
                'NPR' => '&#8360;',
                'NZD' => '&#36;',
                'OMR' => '&#65020;',
                'PAB' => '&#66;&#47;&#46;',
                'PEN' => '&#83;&#47;&#46;',
                'PGK' => '&#75;', // ?
                'PHP' => '&#8369;',
                'PKR' => '&#8360;',
                'PLN' => '&#122;&#322;',
                'PYG' => '&#71;&#115;',
                'QAR' => '&#65020;',
                'RON' => '&#108;&#101;&#105;',
                'RSD' => '&#1044;&#1080;&#1085;&#46;',
                'RUB' => '&#1088;&#1091;&#1073;',
                'RWF' => '&#1585;.&#1587;',
                'SAR' => '&#65020;',
                'SBD' => '&#36;',
                'SCR' => '&#8360;',
                'SDG' => '&#163;', // ?
                'SEK' => '&#107;&#114;',
                'SGD' => '&#36;',
                'SHP' => '&#163;',
                'SLL' => '&#76;&#101;', // ?
                'SOS' => '&#83;',
                'SRD' => '&#36;',
                'STD' => '&#68;&#98;', // ?
                'SVC' => '&#36;',
                'SYP' => '&#163;',
                'SZL' => '&#76;', // ?
                'THB' => '&#3647;',
                'TJS' => '&#84;&#74;&#83;', // ? TJS (guess)
                'TMT' => '&#109;',
                'TND' => '&#1583;.&#1578;',
                'TOP' => '&#84;&#36;',
                'TRY' => '&#8356;', // New Turkey Lira (old symbol used)
                'TTD' => '&#36;',
                'TWD' => '&#78;&#84;&#36;',
                'TZS' => '',
                'UAH' => '&#8372;',
                'UGX' => '&#85;&#83;&#104;',
                'USD' => '&#36;',
                'UYU' => '&#36;&#85;',
                'UZS' => '&#1083;&#1074;',
                'VEF' => '&#66;&#115;',
                'VND' => '&#8363;',
                'VUV' => '&#86;&#84;',
                'WST' => '&#87;&#83;&#36;',
                'XAF' => '&#70;&#67;&#70;&#65;',
                'XCD' => '&#36;',
                'XDR' => '',
                'XOF' => '',
                'XPF' => '&#70;',
                'YER' => '&#65020;',
                'ZAR' => '&#82;',
                'ZMK' => '&#90;&#75;', // ?
                'ZWL' => '&#90;&#36;'
        ];
        return $currencies[$currencyCode];
    }

    
}
