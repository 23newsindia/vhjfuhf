<?php
class MACP_VCL_Generator {
    public static function generate_vcl() {
        $vcl = <<<'VCL'
vcl 4.0;

import std;

# Default backend definition
backend default {
    .host = "127.0.0.1";
    .port = "6081";
}

# ACL for purge requests
acl purge {
    "localhost";
    "127.0.0.1";
}

# Sub for handling PURGE requests
sub vcl_recv {
    # Handle PURGE requests
    if (req.method == "PURGE") {
        if (!client.ip ~ purge) {
            return(synth(405, "Not allowed."));
        }
        
        if (req.http.X-Purge-Method == "regex") {
            ban("obj.http.x-url ~ " + req.url);
            return(synth(200, "Banned"));
        }
        
        return (purge);
    }

    # Add the X-MACP-Cache header based on cookies
    if (req.http.cookie ~ "wordpress_logged_in_") {
        set req.http.X-MACP-Cache = "user-" + regsub(req.http.cookie, ".*wordpress_logged_in_([^%]+).*", "\1");
    } else {
        set req.http.X-MACP-Cache = "guest";
    }

    # Cache different versions based on X-MACP-Cache header
    if (req.http.X-MACP-Cache) {
        hash_data(req.http.X-MACP-Cache);
    }
}

sub vcl_backend_response {
    # Set TTL for cached content
    if (beresp.http.X-MACP-Cache ~ "user-") {
        set beresp.ttl = 5m;  # 5 minutes for logged-in users
    } else {
        set beresp.ttl = 1h;  # 1 hour for guests
    }

    # Store the URL in the object for purging
    set beresp.http.x-url = bereq.url;
}

sub vcl_deliver {
    # Remove the x-url header before delivering to the client
    unset resp.http.x-url;
    
    # Add debug headers if needed
    if (obj.hits > 0) {
        set resp.http.X-Cache = "HIT";
    } else {
        set resp.http.X-Cache = "MISS";
    }
}
VCL;

        return $vcl;
    }
}