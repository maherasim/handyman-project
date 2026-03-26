"""
Convert dr5hn countries+states+cities.json to user's SQL schema.
"""
import gzip
import json

def escape_sql(val):
    if val is None or val == '':
        return "''"
    s = str(val).replace('\\', '\\\\').replace("'", "\\'")
    return f"'{s}'"

def safe_int(val, default=0):
    try:
        if val is None or val == '':
            return default
        s = str(val).split()[0] if isinstance(val, str) else str(val)
        return int(s.replace(' ', ''))
    except (ValueError, TypeError):
        return default

BASE = r'e:\Berlin-Germany\handyman-project\database'

print("Loading dr5hn data...")
with gzip.open(f'{BASE}/dr5hn_full.json.gz', 'rt', encoding='utf-8') as f:
    countries = json.load(f)

countries_rows = []
states_rows = []
cities_rows = []

for country in countries:
    cid = country['id']
    code = country.get('iso2') or (country.get('iso3', '')[:2] if country.get('iso3') else '')
    name = country.get('name', '')
    dial = safe_int(country.get('phonecode'))
    curr_name = (country.get('currency_name') or '')[:20]
    symbol = (country.get('currency_symbol') or '')[:20]
    curr_code = (country.get('currency') or '')[:20]
    
    countries_rows.append((cid, code, name, dial, curr_name, symbol, curr_code))
    
    for state in country.get('states', []):
        sid = state['id']
        sname = state.get('name', '')
        states_rows.append((sid, sname, cid))
        
        for city in state.get('cities', []):
            cities_rows.append((city['id'], city.get('name', ''), sid))

print(f"Countries: {len(countries_rows)}, States: {len(states_rows)}, Cities: {len(cities_rows)}")

def write_sql(path, table, columns, rows, fmt):
    with open(path, 'w', encoding='utf-8') as f:
        f.write("""-- phpMyAdmin SQL Dump - dr5hn/countries-states-cities-database
-- Complete dataset: all countries, states, cities
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
/*!40101 SET NAMES utf8mb4 */;
--
-- Database: `frobst_handy25`
--
CREATE TABLE `%s` (
""" % table)
        if table == 'countries':
            f.write("  `id` int(10) UNSIGNED NOT NULL,\n  `code` varchar(3) NOT NULL,\n  `name` varchar(150) NOT NULL,\n  `dial_code` int(11) NOT NULL,\n  `currency_name` varchar(20) NOT NULL,\n  `symbol` varchar(20) NOT NULL,\n  `currency_code` varchar(20) NOT NULL\n")
        elif table == 'states':
            f.write("  `id` int(10) UNSIGNED NOT NULL,\n  `name` varchar(255) NOT NULL,\n  `country_id` int(11) NOT NULL\n")
        else:
            f.write("  `id` int(10) UNSIGNED NOT NULL,\n  `name` varchar(255) DEFAULT NULL,\n  `state_id` int(11) DEFAULT NULL\n")
        f.write(") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n")
        
        batch_size = 1000
        for i in range(0, len(rows), batch_size):
            batch = rows[i:i+batch_size]
            lines = [fmt(r) for r in batch]
            f.write("INSERT INTO `%s` %s VALUES\n" % (table, columns))
            f.write(',\n'.join(lines))
            f.write(';\n\n')
        f.write('\nALTER TABLE `%s` ADD PRIMARY KEY (`id`);\n' % table)
        max_id = max(r[0] for r in rows)
        f.write('ALTER TABLE `%s` MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=%d;\nCOMMIT;\n' % (table, max_id + 1))

c_fmt = lambda r: f"({r[0]}, {escape_sql(r[1])}, {escape_sql(r[2])}, {r[3]}, {escape_sql(r[4])}, {escape_sql(r[5])}, {escape_sql(r[6])})"
s_fmt = lambda r: f"({r[0]}, {escape_sql(r[1])}, {r[2]})"

write_sql(f'{BASE}/countries_new.sql', 'countries', '(`id`, `code`, `name`, `dial_code`, `currency_name`, `symbol`, `currency_code`)', countries_rows, c_fmt)
write_sql(f'{BASE}/states_new.sql', 'states', '(`id`, `name`, `country_id`)', states_rows, s_fmt)
write_sql(f'{BASE}/cities_new.sql', 'cities', '(`id`, `name`, `state_id`)', cities_rows, s_fmt)

print("Generated: countries_new.sql, states_new.sql, cities_new.sql")
