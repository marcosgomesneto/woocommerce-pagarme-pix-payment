import { registerPaymentMethod } from "@woocommerce/blocks-registry";
import { decodeEntities } from "@wordpress/html-entities";
import { getSetting } from "@woocommerce/settings";
import { __ } from "@wordpress/i18n";

const settings = getSetting("wc_pagarme_pix_payment_geteway_data", {});

const defaultLabel = __("Pagar.me PIX", "wc-pagarme-pix-payment");

const label = decodeEntities(settings.title) || defaultLabel;
/**
 * Content component
 */
const Content = () => {
  return decodeEntities(settings.description || "");
};
/**
 * Label component
 *
 * @param {*} props Props from payment API.
 */
const Label = (props) => {
  const { PaymentMethodLabel } = props.components;
  return <PaymentMethodLabel text={label} />;
};

/**
 * Pagar.me method config object.
 */
const WCPagarmePixPaymentGateway = {
  name: "wc_pagarme_pix_payment_geteway",
  label: <Label />,
  content: <Content />,
  edit: <Content />,
  canMakePayment: () => true,
  ariaLabel: label,
  supports: {
    features: settings.supports,
  },
};

registerPaymentMethod(WCPagarmePixPaymentGateway);
