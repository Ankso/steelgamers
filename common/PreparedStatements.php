<?php
/**
 * This class acts as an enum with all the prepared statements that the application needs.
 * That's why it isn't placed in /classes folder.
 * @author Ankso
 */
class Statements
{
     // Basic load/save user data queries
    const SELECT_USERS_BY_ID              = "SELECT id, username, password_sha1, email, ip_v4, ip_v6, is_online, last_login, active FROM users WHERE id = ?";
    const SELECT_USERS_BY_USERNAME        = "SELECT id, username, password_sha1, email, ip_v4, ip_v6, is_online, last_login, active FROM users WHERE username = ?";
    const REPLACE_USERS                   = "REPLACE INTO users VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    const SELECT_USERS_ID                 = "SELECT id FROM users WHERE username = ?";
    const SELECT_USERS_USERNAME           = "SELECT username FROM users WHERE id = ?";
    // For user's data updates and profile changers functions
    const UPDATE_USERS_ID                 = "UPDATE users SET id = ? WHERE id = ?";
    const UPDATE_USERS_USERNAME           = "UPDATE users SET username = ? WHERE id = ?";
    const UPDATE_USERS_PASSWORD           = "UPDATE users SET password_sha1 = ? WHERE id = ?";
    const UPDATE_USERS_EMAIL              = "UPDATE users SET email = ? WHERE id = ?";
    const UPDATE_USERS_LOCALES            = "UPDATE users SET locales = ? WHERE id = ?";
    const UPDATE_USERS_IPV4               = "UPDATE users SET ip_v4 = ? WHERE id = ?";
    const UPDATE_USERS_IPV6               = "UPDATE users SET ip_v6 = ? WHERE id = ?";
    const UPDATE_USERS_ONLINE             = "UPDATE users SET is_online = ? WHERE id = ?";
    const UPDATE_USERS_LAST_LOGIN         = "UPDATE users SET last_login = ? WHERE id = ?";
    const SELECT_USERS_ACTIVE             = "SELECT active FROM users WHERE id = ?";
    const UPDATE_USERS_ACTIVE             = "UPDATE users SET active = ? WHERE id = ?";
    const INSERT_USERS_RANKS              = "INSERT INTO users_ranks (user_id, rank_mask) VALUES (?, ?)";
    const SELECT_USERS_RANKS              = "SELECT rank_mask FROM users_ranks WHERE user_id = ?";
    const UPDATE_USERS_RANKS              = "UPDATE users_ranks SET rank_mask = ? WHERE user_id = ?"; 
    // Registration management
    const SELECT_USERS_EMAIL              = "SELECT id FROM users WHERE email = ?";
    const INSERT_USERS                    = "INSERT INTO users (username, password_sha1, email, ip_v4, ip_v6, is_online, last_login, active) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    const INSERT_USERS_EMAIL_VERIFICATION = "INSERT INTO users_email_verification (user_id, verification_hash, sended) VALUES (?, ?, ?)";
    const SELECT_USERS_EMAIL_VERIFICATION = "SELECT * FROM users_email_verification WHERE user_id = ?";
    const DELETE_USERS_EMAIL_VERIFICATION = "DELETE FROM users_email_verification WHERE user_id = ?";
    const DELETE_USERS                    = "DELETE FROM users WHERE id = ?";
    // Login system
    const SELECT_USERS_LOGIN_DATA         = "SELECT password_sha1 FROM users WHERE username = ?";
    // News system
    const INSERT_LATEST_NEWS              = "INSERT INTO latest_news (writer_id, writer_name, title, body, timestamp) VALUES (?, ?, ?, ?, ?)";
    const SELECT_LATEST_NEWS              = "SELECT * FROM latest_news ORDER BY timestamp DESC LIMIT "; // Note that this query must be completed with the config option MAX_DISPLAYED_NEWS
    const DELETE_LATEST_NEWS              = "DELETE FROM latest_news WHERE id = ?";
    // FAQ system
    const INSERT_FAQ                      = "INSERT INTO faq (writer_id, writer_name, question, answer, timestamp) VALUES (?, ?, ?, ?, ?)";
    const SELECT_FAQ                      = "SELECT * FROM faq ORDER BY id";
    const DELETE_FAQ                      = "DELETE FROM faq WHERE id = ?";
}
?>