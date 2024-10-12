SELECT users.id, username
                FROM users
                INNER JOIN user_tokens ON user_id = users.id
                WHERE selector = 'c01fc3a294e816de7dd84dd87ffe1071:ab4ad52245504cd253c22d1be03983c372c7971da960ccda4666bcd250c9644b' AND
                      expiry > now()
                LIMIT 1