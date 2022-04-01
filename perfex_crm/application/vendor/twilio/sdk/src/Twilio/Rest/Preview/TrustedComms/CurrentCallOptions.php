<?php

/**
 * This code was generated by
 * \ / _    _  _|   _  _
 * | (_)\/(_)(_|\/| |(/_  v1.0.0
 * /       /
 */

namespace Twilio\Rest\Preview\TrustedComms;

use Twilio\Options;
use Twilio\Values;

/**
 * PLEASE NOTE that this class contains preview products that are subject to change. Use them with caution. If you currently do not have developer preview access, please contact help@twilio.com.
 */
abstract class CurrentCallOptions {
    /**
     * @param string $xXcnamSensitivePhoneNumberFrom The originating Phone Number
     * @param string $xXcnamSensitivePhoneNumberTo The terminating Phone Number
     * @return FetchCurrentCallOptions Options builder
     */
    public static function fetch(string $xXcnamSensitivePhoneNumberFrom = Values::NONE, string $xXcnamSensitivePhoneNumberTo = Values::NONE): FetchCurrentCallOptions {
        return new FetchCurrentCallOptions($xXcnamSensitivePhoneNumberFrom, $xXcnamSensitivePhoneNumberTo);
    }
}

class FetchCurrentCallOptions extends Options {
    /**
     * @param string $xXcnamSensitivePhoneNumberFrom The originating Phone Number
     * @param string $xXcnamSensitivePhoneNumberTo The terminating Phone Number
     */
    public function __construct(string $xXcnamSensitivePhoneNumberFrom = Values::NONE, string $xXcnamSensitivePhoneNumberTo = Values::NONE) {
        $this->options['xXcnamSensitivePhoneNumberFrom'] = $xXcnamSensitivePhoneNumberFrom;
        $this->options['xXcnamSensitivePhoneNumberTo'] = $xXcnamSensitivePhoneNumberTo;
    }

    /**
     * The originating Phone Number, given in [E.164 format](https://www.twilio.com/docs/glossary/what-e164). This phone number should be a Twilio number, otherwise it will return an error with HTTP Status Code 400.
     *
     * @param string $xXcnamSensitivePhoneNumberFrom The originating Phone Number
     * @return $this Fluent Builder
     */
    public function setXXcnamSensitivePhoneNumberFrom(string $xXcnamSensitivePhoneNumberFrom): self {
        $this->options['xXcnamSensitivePhoneNumberFrom'] = $xXcnamSensitivePhoneNumberFrom;
        return $this;
    }

    /**
     * The terminating Phone Number, given in [E.164 format](https://www.twilio.com/docs/glossary/what-e164).
     *
     * @param string $xXcnamSensitivePhoneNumberTo The terminating Phone Number
     * @return $this Fluent Builder
     */
    public function setXXcnamSensitivePhoneNumberTo(string $xXcnamSensitivePhoneNumberTo): self {
        $this->options['xXcnamSensitivePhoneNumberTo'] = $xXcnamSensitivePhoneNumberTo;
        return $this;
    }

    /**
     * Provide a friendly representation
     *
     * @return string Machine friendly representation
     */
    public function __toString(): string {
        $options = \http_build_query(Values::of($this->options), '', ' ');
        return '[Twilio.Preview.TrustedComms.FetchCurrentCallOptions ' . $options . ']';
    }
}