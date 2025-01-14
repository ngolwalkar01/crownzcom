<?php

// File generated from our OpenAPI spec

namespace StellarWP\Learndash\Stripe;

/**
 * @property string $id Unique identifier for the object.
 * @property string $object String representing the object's type. Objects of the same type share the same value.
 * @property int $period_end The end of the invoicing period. This TDS applies to Stripe fees collected during this invoicing period.
 * @property int $period_start The start of the invoicing period. This TDS applies to Stripe fees collected during this invoicing period.
 * @property string $tax_deduction_account_number The TAN that was supplied to Stripe when TDS was assessed
 *
 * @license MIT
 * Modified by learndash on 18-December-2024 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */
class TaxDeductedAtSource extends ApiResource
{
    const OBJECT_NAME = 'tax_deducted_at_source';
}
