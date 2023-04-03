-- Drop foreign keys from echo_push_subscription - T306473
ALTER TABLE /*_*/echo_push_subscription DROP FOREIGN KEY echo_push_subscription_ibfk_1;
