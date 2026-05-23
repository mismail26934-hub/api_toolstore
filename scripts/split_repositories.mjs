import fs from "fs";
import path from "path";
import { fileURLToPath } from "url";

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const root = path.join(__dirname, "..");
const mProsesPath = path.join(root, "model", "m_proses.php");
const src = fs.readFileSync(mProsesPath, "utf8");

const sections = [
  {
    name: "form",
    class: "FormRepository",
    start: "// ------------- TABEL FORM ----------------------------",
    end: "// ------------- TABEL FORM DETAIL ----------------------",
  },
  {
    name: "form_detail",
    class: "FormDetailRepository",
    start: "// ------------- TABEL FORM DETAIL ----------------------",
    end: "// ------------- TABEL ACTION NOTE --------------------",
  },
  {
    name: "action_note",
    class: "ActionNoteRepository",
    start: "// ------------- TABEL ACTION NOTE --------------------",
    end: "// ------------- TABEL PO ----------------------",
  },
  {
    name: "po",
    class: "PoRepository",
    start: "// ------------- TABEL PO ----------------------",
    end: "// ------------- TABEL SO ----------------------",
  },
  {
    name: "so",
    class: "SoRepository",
    start: "// ------------- TABEL SO ----------------------",
    end: "// ------------- TABEL SUPERRIOR ----------------------",
  },
  {
    name: "superior",
    class: "SuperiorRepository",
    start: "// ------------- TABEL SUPERRIOR ----------------------",
    end: "// ------------- TABEL RCV WH ----------------------",
  },
  {
    name: "rcv_wh",
    class: "RcvWhRepository",
    start: "// ------------- TABEL RCV WH ----------------------",
    end: "// ------------- TABEL RCV TOOL ----------------------",
  },
  {
    name: "rcv_tool",
    class: "RcvToolRepository",
    start: "// ------------- TABEL RCV TOOL ----------------------",
    end: "function __destruct",
  },
];

function extractBody(startMarker, endMarker) {
  const start = src.indexOf(startMarker);
  const end = src.indexOf(endMarker, start);
  if (start === -1 || end === -1) {
    throw new Error(`Markers not found: ${startMarker}`);
  }
  let body = src.slice(start + startMarker.length, end);
  body = body.replace(
    /^\s+private function form_list_where_sql/,
    "    private function form_list_where_sql",
  );
  return body.trim();
}

const repoHeader = `<?php

require_once __DIR__ . "/repository_base.php";

`;

for (const sec of sections) {
  const body = extractBody(sec.start, sec.end);
  const content = `${repoHeader}/**\n * Repository ${sec.name.replace(/_/g, " ")}.\n */\nclass ${sec.class} extends RepositoryBase\n{\n${body}\n}\n`;
  const outPath = path.join(root, "model", `${sec.name}_repository.php`);
  fs.writeFileSync(outPath, content);
  console.log("Wrote", outPath);
}

// Update user_repository to extend RepositoryBase
const userPath = path.join(root, "model", "user_repository.php");
let userSrc = fs.readFileSync(userPath, "utf8");
userSrc = userSrc.replace(
  /require_once __DIR__ \. "\/db_table\.php";\nrequire_once __DIR__ \. "\/db_statement\.php";\n\n\/\*\*[\s\S]*?\*\/\nclass UserRepository extends DbTable\n\{\n    use DbStatementTrait;\n\n    \/\*\* @var Dbs \*\/\n    private \$mysqli;\n\n    public function __construct\(\$conn\)\n    \{\n        \$this->mysqli = \$conn;\n    \}/,
  'require_once __DIR__ . "/repository_base.php";\n\nclass UserRepository extends RepositoryBase\n{',
);
userSrc = userSrc.replace(/private \$mysqli;/g, "");
fs.writeFileSync(userPath, userSrc);
console.log("Updated user_repository.php");

console.log("Done. Run generate_proses_facade.mjs next.");
