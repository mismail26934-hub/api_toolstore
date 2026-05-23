import fs from "fs";
import path from "path";
import { fileURLToPath } from "url";

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const root = path.join(__dirname, "..");

const repos = [
  { file: "user_repository.php", class: "UserRepository", prop: "userRepository", method: "users" },
  { file: "form_repository.php", class: "FormRepository", prop: "formRepository", method: "forms" },
  { file: "form_detail_repository.php", class: "FormDetailRepository", prop: "formDetailRepository", method: "formDetails" },
  { file: "action_note_repository.php", class: "ActionNoteRepository", prop: "actionNoteRepository", method: "actionNotes" },
  { file: "po_repository.php", class: "PoRepository", prop: "poRepository", method: "pos" },
  { file: "so_repository.php", class: "SoRepository", prop: "soRepository", method: "sos" },
  { file: "superior_repository.php", class: "SuperiorRepository", prop: "superiorRepository", method: "superiors" },
  { file: "rcv_wh_repository.php", class: "RcvWhRepository", prop: "rcvWhRepository", method: "rcvWhs" },
  { file: "rcv_tool_repository.php", class: "RcvToolRepository", prop: "rcvToolRepository", method: "rcvTools" },
];

function parseMethods(php) {
  const methods = [];
  const re = /public function (\w+)\s*\(/g;
  let m;
  while ((m = re.exec(php)) !== null) {
    const name = m[1];
    if (name === "__construct") {
      continue;
    }
    const start = m.index + m[0].length;
    let depth = 1;
    let i = start;
    while (i < php.length && depth > 0) {
      if (php[i] === "(") {
        depth++;
      }
      if (php[i] === ")") {
        depth--;
      }
      i++;
    }
    const params = php.slice(start, i - 1).replace(/\s+/g, " ").trim();
    methods.push({ name, params });
  }
  return methods;
}

function paramNames(params) {
  if (!params) {
    return [];
  }
  return params
    .split(",")
    .map((p) => {
      const t = p.trim();
      if (t === "") {
        return "";
      }
      const eq = t.indexOf("=");
      const namePart = eq === -1 ? t : t.slice(0, eq).trim();
      return namePart.replace(/^\$/, "");
    })
    .filter((n) => n !== "");
}

const requires = repos
  .map((r) => `require_once __DIR__ . "/${r.file}";`)
  .join("\n");

let props = "";
let getters = "";
let delegations = "";

for (const repo of repos) {
  const php = fs.readFileSync(path.join(root, "model", repo.file), "utf8");
  const methods = parseMethods(php);

  props += `    private ?${repo.class} $${repo.prop} = null;\n\n`;

  getters += `    private function ${repo.method}(): ${repo.class}\n    {\n        if ($this->${repo.prop} === null) {\n            $this->${repo.prop} = new ${repo.class}($this->mysqli);\n        }\n\n        return $this->${repo.prop};\n    }\n\n`;

  delegations += `    // --- ${repo.class} ---\n\n`;

  for (const method of methods) {
    const names = paramNames(method.params);
    const passArgs = names.map((n) => `$${n}`).join(", ");
    const signature = method.params ? method.params : "";
    delegations += `    public function ${method.name}(${signature})\n    {\n        return $this->${repo.method}()->${method.name}(${passArgs});\n    }\n\n`;
  }
}

const out = `<?php

require_once __DIR__ . "/../conn/env_loader.php";
require_once __DIR__ . "/db_table.php";
${requires}

/**
 * Facade akses data — delegasi ke repository per domain.
 */
class Proses_sql extends DbTable
{
    private $mysqli;

${props}    public function __construct($conn)
    {
        $this->mysqli = $conn;
    }

${getters}${delegations}    function __destruct()
    {
        if (
            isset($this->mysqli->conn) &&
            $this->mysqli->conn instanceof mysqli
        ) {
            $this->mysqli->conn->close();
        }
    }
}
`;

fs.writeFileSync(path.join(root, "model", "m_proses.php"), out);
console.log("Wrote model/m_proses.php");
