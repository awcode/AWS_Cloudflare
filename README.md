#AWS_Cloudflare v0.1

This is a PHP class for controlling AWS in combination with Cloudflare.

Currently all features are based around managing the EC2 security groups in relation to the current Cloudflare IP ranges.
Ports can be opened and closed specifically for cloudflare, along with optional manual overrides.

If there is interest in more functionality, I have the need or I have the time, then this class may be further developed with extra features.

To get started check out index.php which contains a set of examples, uncomment to test different rules. Be sure to enter your own ASW credentials and security group details, either in the class constructor or in the separate config file.
