<?php
/**
 * This class acts as an enum with all the prepared statements that the application needs.
 * That's why it isn't placed in /classes folder.
 * @author Ankso
 */
class Statements
{
     // Basic load/save user data queries
    const SELECT_USERS_BY_ID              = "SELECT id, username, password_sha1, email, ip_v4, ip_v6, is_online, last_login, register_date, active FROM users WHERE id = ?";
    const SELECT_USERS_BY_USERNAME        = "SELECT id, username, password_sha1, email, ip_v4, ip_v6, is_online, last_login, register_date, active FROM users WHERE username = ?";
    const REPLACE_USERS                   = "REPLACE INTO users VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
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
    const UPDATE_USERS_REGISTER_DATE      = "UPDATE users SET register_date = ? WHERE id = ?";
    const SELECT_USERS_ACTIVE             = "SELECT active FROM users WHERE id = ?";
    const UPDATE_USERS_ACTIVE             = "UPDATE users SET active = ? WHERE id = ?";
    const INSERT_USERS_RANKS              = "INSERT INTO users_ranks (user_id, rank_mask) VALUES (?, ?)";
    const SELECT_USERS_RANKS              = "SELECT rank_mask FROM users_ranks WHERE user_id = ?";
    const UPDATE_USERS_RANKS              = "UPDATE users_ranks SET rank_mask = ? WHERE user_id = ?"; 
    // Registration management
    const SELECT_USERS_EMAIL              = "SELECT id FROM users WHERE email = ?";
    const INSERT_USERS                    = "INSERT INTO users (username, password_sha1, email, ip_v4, ip_v6, is_online, last_login, register_date, active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
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
    // Users management
    const SELECT_USERS_DATA_ADMIN         = "SELECT a.id, a.username, a.email, a.ip_v4, a.last_login, a.register_date, a.active, b.rank_mask FROM users AS a, users_ranks AS b WHERE a.username LIKE ? AND b.user_id = a.id";
    const SELECT_USERS_BANNED             = "SELECT ban_start, ban_end, ban_reason, banned_by, active FROM users_banned WHERE user_id = ? AND active = 1";
    const SELECT_USERS_BANNED_HISTORY     = "SELECT ban_start, ban_end, ban_reason, banned_by, active FROM users_banned WHERE user_id = ?";
    const INSERT_USERS_BANNED             = "INSERT INTO users_banned (user_id, ban_start, ban_end, ban_reason, banned_by, active) VALUES (?, ?, ?, ?, ?, ?)";
    const UPDATE_USERS_BANNED_STATUS      = "UPDATE users_banned SET active = ? WHERE user_id = ?";
    const DELETE_USERS_BANNED             = "DELETE FROM users_banned WHERE user_id = ?";
    // Premium system
    const SELECT_USERS_PREMIUM            = "SELECT premium_start, premium_end, active FROM users_premium WHERE user_id = ?";
    const SELECT_USERS_PREMIUM_ACTIVE     = "SELECT premium_start, premium_end FROM users_premium WHERE user_id = ? AND active = 1";
    const INSERT_USERS_PREMIUM            = "INSERT INTO users_premium (user_id, premium_start, premium_end, active) VALUES (?, ?, ?, ?)";
    const UPDATE_USERS_PREMIUM_ACTIVE     = "UPDATE users_premium SET active = ? WHERE user_id = ? AND active = ?";
    // Members list
    const SELECT_ALL_MEMBERS              = "SELECT a.username, a.email, a.is_online, a.last_login, a.register_date, b.rank_mask FROM users AS a, users_ranks AS b WHERE a.id = b.user_id AND a.active = 1 LIMIT 100";
    // TS3 related queries
    const INSERT_USERS_TS3_TOKEN          = "INSERT INTO users_ts3_token VALUES (?, ?)";
    const SELECT_USERS_TS3_TOKEN          = "SELECT token FROM users_ts3_token WHERE user_id = ?";
    const DELETE_USERS_TS3_TOKEN          = "DELETE FROM users_ts3_token WHERE user_id = ?";
    // Multimedia system related queries
    const SELECT_MULTIMEDIA               = "SELECT * FROM multimedia ORDER BY guid DESC LIMIT 100";
    const INSERT_MULTIMEDIA               = "INSERT INTO multimedia (uploader, url, media_thumbnail) VALUES (?, ?, ?)";
    const SELECT_USER_MULTIMEDIA          = "SELECT * FROM multimedia WHERE uploader = ? ORDER BY guid DESC";
    // Minecraft (Mitracraft) servers network specific queries
    const SELECT_USER_CHARACTERS          = "SELECT * FROM xauth_account WHERE email = ?";
    const SELECT_USER_CHARACTER_BY_NAME   = "SELECT * FROM xauth_account WHERE playername = ?";
    const UPDATE_USER_CHARACTER_EMAIL     = "UPDATE xauth_account SET email = ? WHERE playername = ?";
    const INSERT_USER_CHARACTER           = "INSERT INTO xauth_account (playername, password, pwtype, email, registerdate, registerip, lastlogindate, lastloginip, active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    const DELETE_USER_CHARACTER           = "DELETE FROM xauth_account WHERE playername = ?";
    const UPDATE_USER_BANNED              = "UPDATE xauth_account SET active = ? WHERE email = ?";
    // WoW servers network specific queries
    const SELECT_USER_WOW_ACCOUNT         = "SELECT id, username, gmlevel, email, joindate, last_ip, last_login, expansion, locale FROM account WHERE username = ?";
    const SELECT_USER_WOW_CHARACTERS      = "SELECT name, race, class, gender, level, money, online, totaltime, arenaPoints, totalHonorPoints, todayHonorPoints, yesterdayHonorPoints, totalKills, todayKills, yesterdayKills FROM characters WHERE account = ?";
    const INSERT_USER_WOW_ACCOUNT         = "INSERT INTO account (username, sha_pass_hash, gmlevel, sessionkey, v, s, email, joindate, last_ip, failed_logins, locked, last_login, active_realm_id, expansion, mutetime, locale) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    const INSERT_USER_WOW_ACCOUNT_BANNED  = "INSERT INTO account_banned (id, bandate, unbandate, bannedby, banreason, active) VALUES(?, ?, ?, ?, ?, ?)";
    const UPDATE_USER_WOW_ACCOUNT_BANNED  = "UPDATE account_banned SET active = ? WHERE id = ?";
    const SELECT_TOTAL_ONLINE_USERS       = "SELECT COUNT(guid) AS totalOnline FROM characters WHERE online = 1";
    const SELECT_USER_WOW_PREMIUM         = "SELECT * FROM account_premium WHERE account = ?";
    const SELECT_USER_WOW_PREMIUM_ACTIVE  = "SELECT * FROM account_premium WHERE account = ? AND active = 1";
    const INSERT_USER_WOW_PREMIUM         = "INSERT INTO account_premium (account, premium_start, premium_end, gold_bonus, reputation_bonus, honor_bonus, arena_bonus, active) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    const UPDATE_USER_WOW_PREMIUM_ACTIVE  = "UPDATE account_premium SET active = ? WHERE account = ? AND active = ?";
    const UPDATE_USER_WOW_PREMIUM_BONUS   = "UPDATE account_premium SET gold_bonus = ?, reputation_bonus = ?, honor_bonus = ?, arena_bonus = ? WHERE account = ?";
    // Minecraft news system
    const INSERT_MINECRAFT_NEWS           = "INSERT INTO minecraft_news (writer_id, writer_name, title, body, timestamp) VALUES (?, ?, ?, ?, ?)";
    const SELECT_MINECRAFT_NEWS           = "SELECT * FROM minecraft_news ORDER BY timestamp DESC LIMIT "; // Note that this query must be completed with the config option MAX_DISPLAYED_NEWS
    const DELETE_MINECRAFT_NEWS           = "DELETE FROM minecraft_news WHERE id = ?";
    // ArmA 2 news system
    const INSERT_ARMA2_NEWS               = "INSERT INTO arma2_news (writer_id, writer_name, title, body, timestamp) VALUES (?, ?, ?, ?, ?)";
    const SELECT_ARMA2_NEWS               = "SELECT * FROM arma2_news ORDER BY timestamp DESC LIMIT "; // Note that this query must be completed with the config option MAX_DISPLAYED_NEWS
    const DELETE_ARMA2_NEWS               = "DELETE FROM arma2_news WHERE id = ?";
    // WoW news system
    const INSERT_WOW_NEWS                 = "INSERT INTO wow_news (writer_id, writer_name, title, body, timestamp) VALUES (?, ?, ?, ?, ?)";
    const SELECT_WOW_NEWS                 = "SELECT * FROM wow_news ORDER BY timestamp DESC LIMIT "; // Note that this query must be completed with the config option MAX_DISPLAYED_NEWS
    const DELETE_WOW_NEWS                 = "DELETE FROM wow_news WHERE id = ?";
    // League of Legends news system
    const INSERT_LOL_NEWS                 = "INSERT INTO lol_news (writer_id, writer_name, title, body, timestamp) VALUES (?, ?, ?, ?, ?)";
    const SELECT_LOL_NEWS                 = "SELECT * FROM lol_news ORDER BY timestamp DESC LIMIT "; // Note that this query must be completed with the config option MAX_DISPLAYED_NEWS
    const DELETE_LOL_NEWS                 = "DELETE FROM lol_news WHERE id = ?";
    // DOTA 2 news system
    const INSERT_DOTA_2_NEWS              = "INSERT INTO dota2_news (writer_id, writer_name, title, body, timestamp) VALUES (?, ?, ?, ?, ?)";
    const SELECT_DOTA_2_NEWS              = "SELECT * FROM dota2_news ORDER BY timestamp DESC LIMIT "; // Note that this query must be completed with the config option MAX_DISPLAYED_NEWS
    const DELETE_DOTA_2_NEWS              = "DELETE FROM dota2_news WHERE id = ?";
    // War Thunder news system
    const INSERT_WAR_THUNDER_NEWS         = "INSERT INTO warthunder_news (writer_id, writer_name, title, body, timestamp) VALUES (?, ?, ?, ?, ?)";
    const SELECT_WAR_THUNDER_NEWS         = "SELECT * FROM warthunder_news ORDER BY timestamp DESC LIMIT ";
    const DELETE_WAR_THUNDER_NEWS         = "DELETE FROM warthunder_news WHERE id = ?";
    // Archeage news system
    const INSERT_WAR_THUNDER_NEWS         = "INSERT INTO archeage_news (writer_id, writer_name, title, body, timestamp) VALUES (?, ?, ?, ?, ?)";
    const SELECT_WAR_THUNDER_NEWS         = "SELECT * FROM archeage_news ORDER BY timestamp DESC LIMIT ";
    const DELETE_WAR_THUNDER_NEWS         = "DELETE FROM archeage_news WHERE id = ?";
}
?>